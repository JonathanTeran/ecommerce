@extends('emails.layout')

@section('title', 'Confirmacion de Pedido')

@section('content')
    <h2>Pedido Confirmado</h2>
    <p>Hola {{ $order->user->name }}, tu pedido ha sido recibido exitosamente.</p>

    <div class="highlight-box">
        <p class="label">Numero de Pedido</p>
        <p class="value">{{ $order->order_number }}</p>
    </div>

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
            <td style="text-align: right; padding: 6px 0; color: #6b7280; font-size: 14px;">Subtotal</td>
            <td style="text-align: right; padding: 6px 0; font-size: 14px; width: 120px;">${{ number_format($order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: right; padding: 6px 0; color: #6b7280; font-size: 14px;">IVA</td>
            <td style="text-align: right; padding: 6px 0; font-size: 14px;">${{ number_format($order->tax_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: right; padding: 12px 0 6px; border-top: 2px solid #1e40af; color: #1e40af; font-size: 18px; font-weight: 700;">Total</td>
            <td style="text-align: right; padding: 12px 0 6px; border-top: 2px solid #1e40af; color: #1e40af; font-size: 18px; font-weight: 700;">${{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    <p>Te mantendremos informado sobre el estado de tu pedido.</p>
@endsection
