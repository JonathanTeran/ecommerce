@extends('emails.layout')

@section('title', 'Bienvenido')

@section('content')
    <h2>Bienvenido, {{ $user->name }}!</h2>
    <p>Gracias por registrarte en {{ $settings?->site_name ?? config('app.name') }}.</p>

    <p>Tu cuenta ha sido creada exitosamente. Ahora puedes:</p>

    <ul style="color: #4b5563; font-size: 15px; line-height: 2;">
        <li>Explorar nuestro catalogo de productos</li>
        <li>Agregar productos a tu lista de deseos</li>
        <li>Solicitar cotizaciones</li>
        <li>Realizar pedidos</li>
    </ul>

    <p>Esperamos que disfrutes tu experiencia de compra.</p>
@endsection
