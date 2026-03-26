@extends('emails.layout')

@section('title', 'Pedido Enviado')

@section('content')
    <h2>Tu pedido ha sido enviado</h2>
    <p>Hola {{ $order->user->name }}, tu pedido esta en camino.</p>

    <div class="highlight-box">
        <p class="label">Numero de Pedido</p>
        <p class="value">{{ $order->order_number }}</p>
    </div>

    @if($order->tracking_number)
        <div class="alert-success">
            <p><strong>Numero de Rastreo:</strong> {{ $order->tracking_number }}</p>
            @if($order->tracking_url)
                <p style="margin-top: 8px;">
                    <a href="{{ $order->tracking_url }}" style="color: #166534; font-weight: 600;">Rastrear mi pedido</a>
                </p>
            @endif
        </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; margin: 20px 0;">
        <tr>
            <td style="text-align: right; padding: 12px 0 6px; border-top: 2px solid #1e40af; color: #1e40af; font-size: 18px; font-weight: 700;">Total</td>
            <td style="text-align: right; padding: 12px 0 6px; border-top: 2px solid #1e40af; color: #1e40af; font-size: 18px; font-weight: 700; width: 120px;">${{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    @if($order->shipping_address)
        <p style="font-size: 13px; color: #6b7280;">
            <strong>Direccion de envio:</strong> {{ $order->shipping_full_address }}
        </p>
    @endif

    <p>Te notificaremos cuando tu pedido haya sido entregado.</p>
@endsection
