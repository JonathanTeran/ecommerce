<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PayPhoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayPhoneController extends Controller
{
    /**
     * Handle PayPhone callback after payment attempt.
     * Requires authentication and validates order ownership.
     */
    public function callback(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('checkout.index')
                ->with('error', 'Sesion expirada. Inicia sesion e intenta de nuevo.');
        }

        $paymentId = $request->query('id') ?? $request->query('paymentId');
        $clientTxId = $request->query('clientTransactionId');

        if (! $clientTxId || ! $paymentId) {
            return redirect()->route('checkout.index')
                ->with('error', 'Transaccion invalida.');
        }

        $order = Order::where('id', $clientTxId)
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

        $service = app(PayPhoneService::class);
        $result = $service->confirmTransaction((int) $paymentId);

        // PayPhone statusCode: 3 = Approved, 2 = Cancelled, 1 = Pending
        if ($result['statusCode'] === 3) {
            DB::transaction(function () use ($order, $result) {
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
                    'transaction_id' => $result['transactionId'],
                    'gateway' => 'payphone',
                    'method' => 'payphone',
                    'amount' => $order->total,
                    'currency' => $order->currency ?? 'USD',
                    'status' => \App\Enums\PaymentStatus::COMPLETED,
                    'gateway_response' => $result,
                    'paid_at' => now(),
                    'tenant_id' => $order->tenant_id,
                ]);
            });

            return redirect()->route('checkout.confirmation', $order)
                ->with('success', 'Pago procesado correctamente con PayPhone.');
        }

        $order->update([
            'payment_status' => \App\Enums\PaymentStatus::FAILED,
        ]);

        return redirect()->route('checkout.index')
            ->with('error', 'El pago no fue aprobado. Por favor intente nuevamente.');
    }

    /**
     * Handle PayPhone cancellation.
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
