@extends('emails.layout')

@section('title', 'Pedido Cancelado')

@section('content')
    <h2>Tu pedido ha sido cancelado</h2>
    <p>Hola {{ $order->user->name }}, lamentamos informarte que tu pedido ha sido cancelado.</p>

    <div class="highlight-box">
        <p class="label">Numero de Pedido</p>
        <p class="value">{{ $order->order_number }}</p>
    </div>

    @if($order->cancellation_reason)
        <div class="alert-danger">
            <p><strong>Razon:</strong> {{ $order->cancellation_reason }}</p>
        </div>
    @endif

    <table style="width: 100%; margin: 20px 0;">
        <tr>
            <td style="text-align: right; padding: 12px 0 6px; border-top: 2px solid #dc2626; color: #dc2626; font-size: 18px; font-weight: 700;">Total cancelado</td>
            <td style="text-align: right; padding: 12px 0 6px; border-top: 2px solid #dc2626; color: #dc2626; font-size: 18px; font-weight: 700; width: 120px;">${{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    <p>Si tienes alguna pregunta sobre esta cancelacion, por favor contactanos.</p>
@endsection
