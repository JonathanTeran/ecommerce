@extends('emails.layout')

@section('title', 'Cuenta Aprobada')

@section('content')
    <h2>¡Tu cuenta ha sido aprobada!</h2>
    <p>Hola {{ $user->name }}, nos complace informarte que tu cuenta ha sido aprobada exitosamente.</p>

    <div class="alert-success">
        <p>Ya puedes iniciar sesión y disfrutar de todos los servicios de {{ $settings?->site_name ?? config('app.name') }}.</p>
    </div>

    <p>Ahora puedes:</p>

    <ul style="color: #4b5563; font-size: 15px; line-height: 2;">
        <li>Explorar nuestro catálogo de productos</li>
        <li>Agregar productos a tu lista de deseos</li>
        <li>Solicitar cotizaciones</li>
        <li>Realizar pedidos</li>
    </ul>

    <p style="text-align: center; margin-top: 30px;">
        <a href="{{ url('/') }}" class="btn">Ir a la Tienda</a>
    </p>
@endsection
