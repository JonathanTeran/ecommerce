<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Mail\OrderConfirmationMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Services\ShippingService;
use App\Services\TenantMailService;
use App\Support\LegalAcceptance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Checkout', description: 'Proceso de compra y creación de órdenes')]
class CheckoutController extends Controller
{
    public function index()
    {
        $settings = \App\Models\GeneralSetting::cached();
        $quotationsEnabled = $settings?->isQuotationsEnabled() ?? true;
        $quotationOnlyMode = $settings?->isQuotationOnlyMode() ?? false;
        $paymentMethods = $quotationOnlyMode ? collect() : ($settings ? $settings->getAvailablePaymentMethods() : collect());
        $gatewayConfig = $settings?->getPaymentGatewaysConfig() ?? [];

        // Get available shipping rates
        $shippingService = app(ShippingService::class);
        $shippingRates = $shippingService->getAvailableRates(0);

        return view('checkout.index', compact('paymentMethods', 'gatewayConfig', 'shippingRates', 'quotationOnlyMode', 'quotationsEnabled'));
    }

    public function shippingRates(Request $request): \Illuminate\Http\JsonResponse
    {
        $orderAmount = (float) $request->query('amount', 0);
        $zone = $request->query('zone');

        $shippingService = app(ShippingService::class);
        $rates = $shippingService->getAvailableRates($orderAmount, null, $zone);

        return response()->json($rates);
    }

    #[OA\Post(
        path: '/checkout',
        summary: 'Crear orden',
        description: 'Crea una orden a partir del carrito actual. Soporta múltiples pasarelas de pago (transferencia, Nuvei, PayPhone, Kushki). Requiere autenticación.',
        tags: ['Checkout'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/CheckoutRequest')
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Orden creada exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/OrderResponse')),
            new OA\Response(response: 400, description: 'Carrito vacío o stock insuficiente'),
            new OA\Response(response: 401, description: 'Autenticación requerida'),
        ]
    )]
    public function placeOrder(PlaceOrderRequest $request)
    {
        $user = $request->user('sanctum') ?? $request->user();

        $sessionId = $request->header('X-Session-ID') ?? $request->cookie('cart_session_id');
        $cart = null;

        if ($user) {
            // First try to find cart by user
            $cart = Cart::forUser($user->id)->with('items')->first();

            // If no user cart, try session cart and assign to user
            if ((! $cart || $cart->items->isEmpty()) && $sessionId) {
                $sessionCart = Cart::forSession($sessionId)->with('items')->first();
                if ($sessionCart && $sessionCart->items->isNotEmpty()) {
                    $sessionCart->update(['user_id' => $user->id]);
                    $cart = $sessionCart;
                }
            }
        } elseif ($sessionId) {
            $cart = Cart::forSession($sessionId)->with('items')->first();
        }

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        if (! $user) {
            return response()->json([
                'message' => 'Debes iniciar sesion para completar tu compra.',
                'action' => 'login_required',
            ], 401);
        }

        $validated = $request->validated();

        // Resolve payment method from enum + GeneralSettings config
        $paymentMethodEnum = \App\Enums\PaymentMethod::tryFrom($validated['payment_method']);
        if (! $paymentMethodEnum) {
            return response()->json(['message' => 'Payment method unavailable'], 400);
        }

        $settings = \App\Models\GeneralSetting::cached();
        $availableMethods = $settings ? $settings->getAvailablePaymentMethods() : collect();
        $paymentMethod = $availableMethods->firstWhere('key', $paymentMethodEnum->value);

        if (! $paymentMethod) {
            return response()->json(['message' => 'Payment method unavailable'], 400);
        }

        if ($paymentMethod->requires_proof && ! $request->hasFile('payment_proof')) {
            return response()->json(['message' => 'Payment proof required'], 400);
        }

        // Upload Proof (only for methods that require it)
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            if (! app()->bound('current_tenant') || ! app('current_tenant')) {
                return response()->json(['message' => 'Tenant context required'], 400);
            }
            $tenantDir = 'tenant-' . app('current_tenant')->id;
            $proofPath = $request->file('payment_proof')->store($tenantDir . '/payment_proofs', 'public');
        }

        // Load cart with all relations needed
        $cart->load(['items.product', 'coupon']);

        // Atomic order creation with pessimistic locking on stock
        try {
            $order = DB::transaction(function () use ($cart, $user, $paymentMethod, $paymentMethodEnum, $proofPath, $validated, $request) {
                // Lock products and validate stock atomically
                $productIds = $cart->items->pluck('product_id')->toArray();
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

                foreach ($cart->items as $item) {
                    $product = $products->get($item->product_id);

                    if (! $product || ! $product->is_active) {
                        throw new \RuntimeException('Uno o mas productos no estan disponibles.');
                    }

                    if ($product->quantity < $item->quantity) {
                        throw new \RuntimeException("Stock insuficiente para {$product->name}.");
                    }
                }

                // Calculate shipping cost
                $shippingAmount = 0;
                if (! empty($validated['shipping_rate_key'])) {
                    $shippingAmount = app(ShippingService::class)->getRatePrice($validated['shipping_rate_key']);
                }

                // Calculate Totals
                $cartSubtotal = $cart->subtotal;
                $discountAmount = $cart->discount_amount;
                $cartTaxAmount = $cart->tax_amount;
                $cartTotal = $cart->total + $shippingAmount;
                $surchargeAmount = $cartTotal * ($paymentMethod->surcharge_percentage / 100);
                $finalTotal = $cartTotal + $surchargeAmount;

                // Create Order
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => \App\Enums\OrderStatus::PENDING,
                    'payment_status' => \App\Enums\PaymentStatus::PENDING,
                    'subtotal' => $cartSubtotal,
                    'discount_amount' => $discountAmount,
                    'coupon_code' => $cart->coupon?->code,
                    'tax_amount' => $cartTaxAmount,
                    'shipping_amount' => $shippingAmount,
                    'total' => $finalTotal,
                    'payment_method' => $paymentMethodEnum,
                    'payment_proof_path' => $proofPath,
                    'surcharge_amount' => $surchargeAmount,
                    'shipping_address' => $validated['shipping_address'],
                    'billing_address' => $validated['billing_address'],
                    'legal_acceptance' => LegalAcceptance::snapshot($request),
                    'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                    'placed_at' => now(),
                ]);

                // Increment coupon usage
                if ($cart->coupon) {
                    $cart->coupon->increment('usage_count');
                }

                // Move items and deduct stock atomically
                $inventoryService = app(\App\Services\InventoryService::class);

                foreach ($cart->items as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'sku' => $item->product->sku ?? 'N/A',
                        'name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                        'options' => $item->options,
                    ]);

                    $product = $products->get($item->product_id);
                    if ($product) {
                        $inventoryService->removeStock($product, $item->quantity, 'sale', $order);
                    }
                }

                // Clear Cart
                $cart->items()->delete();
                $cart->delete();

                return $order;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        // Send order confirmation email
        app(TenantMailService::class)->send(new OrderConfirmationMail($order));

        // Award loyalty points
        $loyaltyService = app(\App\Services\LoyaltyService::class);
        if ($loyaltyService->isActive()) {
            $loyaltyService->awardPoints($user, $order);
        }

        // Handle Nuvei Payment (redirect-based, no card data in backend)
        if ($paymentMethod->gateway === 'nuvei') {
            $nuveiService = new \App\Services\NuveiService;
            $paymentData = $nuveiService->createPayment($order, [
                'billing_address' => $validated['billing_address'] ?? [],
                'email' => $validated['shipping_address']['email'] ?? $user->email,
            ]);

            if (! empty($paymentData['redirect_url'])) {
                return response()->json([
                    'message' => 'Redirecting to payment gateway',
                    'redirect' => $paymentData['redirect_url'],
                    'order_id' => $order->id,
                ]);
            }

            // Simulation mode (local dev without real credentials)
            if ($paymentData['status'] === 'success') {
                $order->update([
                    'payment_status' => \App\Enums\PaymentStatus::COMPLETED,
                    'status' => \App\Enums\OrderStatus::PROCESSING,
                ]);

                return response()->json([
                    'message' => 'Payment processed successfully',
                    'redirect' => route('checkout.confirmation', $order),
                    'order_id' => $order->id,
                ]);
            }
        }

        // Handle PayPhone Payment
        if ($paymentMethod->gateway === 'payphone') {
            $payPhoneService = app(\App\Services\PayPhoneService::class);
            $paymentData = $payPhoneService->createPayment($order);

            if ($paymentData['status'] === 'redirect' && ! empty($paymentData['redirect_url'])) {
                return response()->json([
                    'message' => 'Redirecting to PayPhone',
                    'redirect' => $paymentData['redirect_url'],
                    'order_id' => $order->id,
                ]);
            }

            // Simulation mode (no real PayPhone token configured)
            if ($paymentData['status'] === 'success') {
                $order->update([
                    'payment_status' => \App\Enums\PaymentStatus::COMPLETED,
                    'status' => \App\Enums\OrderStatus::PROCESSING,
                ]);

                return response()->json([
                    'message' => 'Payment processed successfully',
                    'redirect' => route('checkout.confirmation', $order),
                    'order_id' => $order->id,
                ]);
            }
        }

        // Handle Kushki Payment
        if ($paymentMethod->gateway === 'kushki') {
            $kushkiService = app(\App\Services\KushkiService::class);
            $paymentData = $kushkiService->createPayment($order);

            if ($paymentData['status'] === 'redirect' && ! empty($paymentData['redirect_url'])) {
                return response()->json([
                    'message' => 'Redirecting to Kushki',
                    'redirect' => $paymentData['redirect_url'],
                    'order_id' => $order->id,
                ]);
            }

            // Simulation mode
            if ($paymentData['status'] === 'success') {
                $order->update([
                    'payment_status' => \App\Enums\PaymentStatus::COMPLETED,
                    'status' => \App\Enums\OrderStatus::PROCESSING,
                ]);

                return response()->json([
                    'message' => 'Payment processed successfully',
                    'redirect' => route('checkout.confirmation', $order),
                    'order_id' => $order->id,
                ]);
            }
        }

        return response()->json([
            'message' => 'Order placed successfully',
            'redirect' => route('checkout.confirmation', $order),
            'order_id' => $order->id,
        ]);
    }

    public function confirmation(Order $order)
    {
        Gate::authorize('view', $order);

        $order->load(['items.product']);

        return view('checkout.confirmation', compact('order'));
    }
}
