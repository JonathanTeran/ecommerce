@extends('emails.layout')

@section('title', 'Pedido Entregado')

@section('content')
    <h2>Tu pedido ha sido entregado</h2>
    <p>Hola {{ $order->user->name }}, tu pedido ha sido entregado exitosamente.</p>

    <div class="highlight-box">
        <p class="label">Numero de Pedido</p>
        <p class="value">{{ $order->order_number }}</p>
    </div>

    <div class="alert-success">
        <p>Tu pedido fue entregado el {{ $order->delivered_at?->format('d/m/Y H:i') }}.</p>
    </div>

    <p>Esperamos que disfrutes tu compra. Si tienes alguna pregunta o inconveniente, no dudes en contactarnos.</p>
@endsection
