@extends('emails.layout')

@section('title', 'Bienvenido al Newsletter')

@section('content')
    <h2>Gracias por suscribirte!</h2>
    <p>Te has suscrito exitosamente al newsletter de {{ $settings?->site_name ?? config('app.name') }}.</p>

    <p>A partir de ahora recibirás:</p>

    <ul style="color: #4b5563; font-size: 15px; line-height: 2;">
        <li>Ofertas exclusivas y descuentos</li>
        <li>Novedades y nuevos productos</li>
        <li>Contenido especial para suscriptores</li>
    </ul>

    <p style="margin-top: 24px; font-size: 12px; color: #9ca3af;">
        Si no deseas recibir más correos, puedes
        <a href="{{ url('/newsletter/unsubscribe?email=' . urlencode($subscriber->email) . '&token=' . $subscriber->id) }}"
           style="color: #6b7280; text-decoration: underline;">
            darte de baja aquí
        </a>.
    </p>
@endsection
