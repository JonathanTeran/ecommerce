<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuotationStatus;
use App\Mail\QuotationApprovedMail;
use App\Mail\QuotationReceivedMail;
use App\Mail\QuotationRejectedMail;
use App\Models\Cart;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function createFromCart(Cart $cart, User $user, array $customerData): Quotation
    {
        return DB::transaction(function () use ($cart, $user, $customerData) {
            $cart->load('items.product');

            $subtotal = (float) $cart->subtotal;
            $taxRate = GeneralSetting::first()?->tax_rate ?? 15.00;
            $taxAmount = $subtotal * ($taxRate / 100);
            $total = $subtotal + $taxAmount;

            $quotation = Quotation::create([
                'user_id' => $user->id,
                'status' => QuotationStatus::Pending,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'customer_name' => $customerData['customer_name'],
                'customer_email' => $customerData['customer_email'],
                'customer_phone' => $customerData['customer_phone'] ?? null,
                'customer_company' => $customerData['customer_company'] ?? null,
                'customer_notes' => $customerData['customer_notes'] ?? null,
                'shipping_address' => $customerData['shipping_address'] ?? null,
                'billing_address' => $customerData['billing_address'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $quotation->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'name' => $item->product->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku ?? 'N/A',
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'options' => $item->options,
                ]);
            }

            $cart->clear();

            app(TenantMailService::class)->send(new QuotationReceivedMail($quotation));

            return $quotation;
        });
    }

    public function approve(Quotation $quotation, User $admin): void
    {
        $quotation->approve($admin);

        app(TenantMailService::class)->send(new QuotationApprovedMail($quotation));
    }

    public function reject(Quotation $quotation, User $admin, string $reason): void
    {
        $quotation->reject($admin, $reason);

        app(TenantMailService::class)->send(new QuotationRejectedMail($quotation));
    }

    public function convertToOrder(Quotation $quotation): Order
    {
        if (! $quotation->is_convertible) {
            throw new \RuntimeException('Quotation cannot be converted to order.');
        }

        if (! $quotation->status->canTransitionTo(QuotationStatus::Converted)) {
            throw new \RuntimeException("No se puede convertir una cotización con estado: {$quotation->status->getLabel()}");
        }

        return DB::transaction(function () use ($quotation) {
            $quotation->load('items');

            // Validate stock availability before conversion
            foreach ($quotation->items as $item) {
                $product = Product::find($item->product_id);
                if ($product && $product->quantity < $item->quantity) {
                    throw new \RuntimeException(
                        "Stock insuficiente para '{$product->name}': disponible {$product->quantity}, requerido {$item->quantity}"
                    );
                }
            }

            $order = Order::create([
                'user_id' => $quotation->user_id,
                'status' => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::PENDING,
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount ?? 0,
                'tax_amount' => $quotation->tax_amount,
                'total' => $quotation->total,
                'shipping_address' => $quotation->shipping_address,
                'billing_address' => $quotation->billing_address,
                'notes' => 'Convertido desde cotización ' . $quotation->quotation_number,
                'placed_at' => now(),
            ]);

            $inventoryService = app(InventoryService::class);

            foreach ($quotation->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'options' => $item->options,
                ]);

                $product = Product::find($item->product_id);
                if ($product) {
                    $inventoryService->removeStock(
                        $product,
                        $item->quantity,
                        'sale',
                        $order
                    );
                }
            }

            $quotation->update([
                'status' => QuotationStatus::Converted,
                'converted_order_id' => $order->id,
                'converted_at' => now(),
            ]);

            return $order;
        });
    }

    public function generatePdf(Quotation $quotation): \Barryvdh\DomPDF\PDF
    {
        $quotation->load(['items.product', 'user']);

        $settings = GeneralSetting::first();

        return Pdf::loadView('quotations.pdf', [
            'quotation' => $quotation,
            'settings' => $settings,
        ])->setPaper('a4');
    }
}
