<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class NuveiController extends Controller
{
    public function success(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()?->id, 403);

        if ($order->payment_status !== \App\Enums\PaymentStatus::COMPLETED) {
            $order->update([
                'payment_status' => \App\Enums\PaymentStatus::COMPLETED,
                'status' => \App\Enums\OrderStatus::PROCESSING,
            ]);
        }

        return redirect()->route('checkout.confirmation', $order)
            ->with('success', 'Pago procesado correctamente con Nuvei.');
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()?->id, 403);

        $order->update([
            'payment_status' => \App\Enums\PaymentStatus::FAILED,
            'status' => \App\Enums\OrderStatus::CANCELLED,
        ]);

        return redirect()->route('checkout.index')
            ->with('error', 'El pago fue cancelado o rechazado.');
    }
}
