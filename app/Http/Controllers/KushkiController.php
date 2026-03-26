<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\KushkiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KushkiController extends Controller
{
    /**
     * Handle Kushki callback after payment attempt.
     * Requires authentication and validates order ownership.
     */
    public function callback(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('checkout.index')
                ->with('error', 'Sesion expirada. Inicia sesion e intenta de nuevo.');
        }

        $ticketNumber = $request->query('ticketNumber');
        $orderId = $request->query('order');

        if (! $orderId || ! $ticketNumber) {
            return redirect()->route('checkout.index')
                ->with('error', 'Transaccion invalida.');
        }

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (! $order) {
            return redirect()->route('checkout.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Idempotency: if already completed, just redirect
        if ($order->payment_status === \App\Enums\PaymentStatus::COMPLETED) {
            return redirect()->route('checkout.confirmation', $order)
                ->with('success', 'Pago ya procesado.');
        }

        $kushkiService = app(KushkiService::class);
        $result = $kushkiService->confirmTransaction($ticketNumber);

        if ($result['isSuccessful'] ?? false) {
            DB::transaction(function () use ($order, $result, $ticketNumber) {
                $order->lockForUpdate();

                // Double-check idempotency inside transaction
                if ($order->payment_status === \App\Enums\PaymentStatus::COMPLETED) {
                    return;
                }

                $order->update([
                    'payment_status' => \App\Enums\PaymentStatus::COMPLETED,
                    'status' => \App\Enums\OrderStatus::PROCESSING,
                ]);

                $order->payments()->create([
                    'transaction_id' => $result['ticketNumber'] ?? $ticketNumber,
                    'gateway' => 'kushki',
                    'method' => 'kushki',
                    'amount' => $order->total,
                    'currency' => $order->currency ?? 'USD',
                    'status' => \App\Enums\PaymentStatus::COMPLETED,
                    'gateway_response' => $result,
                    'paid_at' => now(),
                    'tenant_id' => $order->tenant_id,
                ]);
            });

            return redirect()->route('checkout.confirmation', $order)
                ->with('success', 'Pago procesado correctamente con Kushki.');
        }

        $order->update([
            'payment_status' => \App\Enums\PaymentStatus::FAILED,
        ]);

        return redirect()->route('checkout.index')
            ->with('error', 'El pago no pudo ser procesado. Intente nuevamente.');
    }

    /**
     * Handle Kushki cancellation.
     */
    public function cancel(Request $request, Order $order): \Illuminate\Http\RedirectResponse
    {
        abort_unless($order->user_id === $request->user()?->id, 403);

        $order->update([
            'payment_status' => \App\Enums\PaymentStatus::FAILED,
            'status' => \App\Enums\OrderStatus::CANCELLED,
        ]);

        return redirect()->route('checkout.index')
            ->with('error', 'El pago fue cancelado.');
    }
}
