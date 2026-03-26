<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Carrito', description: 'Gestión del carrito de compras')]
class CartController extends Controller
{
    private function getCart(Request $request): Cart
    {
        $user = $request->user('sanctum');
        $sessionId = $request->header('X-Session-ID') ?? $request->cookie('cart_session_id');

        if ($user) {
            $cart = Cart::forUser($user->id)->first();

            // Merge guest cart if exists
            if ($sessionId && $guestCart = Cart::forSession($sessionId)->first()) {
                if (! $cart) {
                    $guestCart->assignToUser($user);
                    $cart = $guestCart;
                } elseif ($guestCart->id !== $cart->id) {
                    $cart->mergeWith($guestCart);
                }
            }

            if (! $cart) {
                $cart = Cart::create(['user_id' => $user->id]);
            }
        } else {
            if (! $sessionId) {
                $sessionId = Str::uuid()->toString();
            }

            $cart = Cart::forSession($sessionId)->firstOrCreate([
                'session_id' => $sessionId,
            ]);
        }

        return $cart;
    }

    #[OA\Get(
        path: '/cart',
        summary: 'Ver carrito',
        description: 'Retorna el contenido actual del carrito. Soporta carrito por sesión (guest) o por usuario autenticado.',
        tags: ['Carrito'],
        parameters: [
            new OA\Parameter(name: 'X-Session-ID', in: 'header', description: 'ID de sesión para carrito de invitado', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Datos del carrito', content: new OA\JsonContent(ref: '#/components/schemas/CartResponse')),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);

        return response()->json([
            'data' => $cart->toCheckoutData(),
            'session_id' => $cart->session_id,
        ]);
    }

    #[OA\Post(
        path: '/cart',
        summary: 'Agregar al carrito',
        description: 'Agrega un producto al carrito con la cantidad especificada.',
        tags: ['Carrito'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CartAddRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Producto agregado', content: new OA\JsonContent(ref: '#/components/schemas/CartResponse')),
            new OA\Response(response: 422, description: 'Producto no encontrado o no disponible'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'integer|min:1',
            'variant_id' => 'nullable|exists:product_variants,id',
            'options' => 'nullable|array',
        ]);

        $product = Product::where('is_active', true)->find($request->product_id);

        if (! $product) {
            return response()->json(['message' => __('Product not found or unavailable.')], 422);
        }

        $cart = $this->getCart($request);
        $variant = $request->variant_id ? ProductVariant::find($request->variant_id) : null;

        $cart->addItem(
            $product,
            $request->quantity ?? 1,
            $variant,
            $request->options ?? []
        );

        return response()->json([
            'message' => 'Item added to cart',
            'data' => $cart->refresh()->toCheckoutData(),
            'session_id' => $cart->session_id,
        ]);
    }

    #[OA\Put(
        path: '/cart/{itemId}',
        summary: 'Actualizar cantidad',
        description: 'Actualiza la cantidad de un item del carrito. Enviar 0 para eliminar.',
        tags: ['Carrito'],
        parameters: [
            new OA\Parameter(name: 'itemId', in: 'path', required: true, description: 'ID del item en el carrito', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CartUpdateRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Carrito actualizado', content: new OA\JsonContent(ref: '#/components/schemas/CartResponse')),
        ]
    )]
    public function update(Request $request, int $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = $this->getCart($request);
        $cart->updateItem($itemId, $request->quantity);

        return response()->json([
            'data' => $cart->refresh()->toCheckoutData(),
            'session_id' => $cart->session_id,
        ]);
    }

    #[OA\Delete(
        path: '/cart/{itemId}',
        summary: 'Eliminar del carrito',
        description: 'Elimina un item específico del carrito.',
        tags: ['Carrito'],
        parameters: [
            new OA\Parameter(name: 'itemId', in: 'path', required: true, description: 'ID del item a eliminar', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Item eliminado', content: new OA\JsonContent(ref: '#/components/schemas/CartResponse')),
        ]
    )]
    public function destroy(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->getCart($request);
        $cart->removeItem($itemId);

        return response()->json([
            'data' => $cart->refresh()->toCheckoutData(),
            'session_id' => $cart->session_id,
        ]);
    }

    #[OA\Post(
        path: '/cart/apply-coupon',
        summary: 'Aplicar cupón',
        description: 'Aplica un código de cupón de descuento al carrito.',
        tags: ['Carrito'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CouponRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Cupón aplicado', content: new OA\JsonContent(ref: '#/components/schemas/CartResponse')),
            new OA\Response(response: 404, description: 'Cupón no encontrado'),
            new OA\Response(response: 422, description: 'Cupón no válido o expirado'),
        ]
    )]
    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $cart = $this->getCart($request);
        $coupon = Coupon::where('code', $request->code)->first();

        if (! $coupon) {
            return response()->json(['message' => 'Cupón no encontrado'], 404);
        }

        if (! $coupon->isValidFor($cart->subtotal, $cart->user_id)) {
            return response()->json(['message' => 'Cupón no válido o expirado'], 422);
        }

        $cart->applyCoupon($coupon);

        return response()->json([
            'message' => 'Cupón aplicado',
            'data' => $cart->refresh()->toCheckoutData(),
            'session_id' => $cart->session_id,
        ]);
    }

    #[OA\Delete(
        path: '/cart/coupon',
        summary: 'Remover cupón',
        description: 'Remueve el cupón de descuento aplicado al carrito.',
        tags: ['Carrito'],
        responses: [
            new OA\Response(response: 200, description: 'Cupón removido', content: new OA\JsonContent(ref: '#/components/schemas/CartResponse')),
        ]
    )]
    public function removeCoupon(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $cart->removeCoupon();

        return response()->json([
            'message' => 'Cupón removido',
            'data' => $cart->refresh()->toCheckoutData(),
            'session_id' => $cart->session_id,
        ]);
    }
}
