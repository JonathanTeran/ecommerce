@extends('emails.layout')

@section('title', 'Registro Recibido')

@section('content')
    <h2>¡Gracias por registrarte, {{ $user->name }}!</h2>
    <p>Hemos recibido tu solicitud de registro en {{ $settings?->site_name ?? config('app.name') }}.</p>

    <div class="highlight-box">
        <p class="label">Estado de tu cuenta</p>
        <p class="value">Pendiente de Aprobación</p>
    </div>

    <p>Tu cuenta será revisada por nuestro equipo. Recibirás un correo de confirmación cuando sea aprobada.</p>
    <p>Una vez aprobada, podrás iniciar sesión y acceder a todos nuestros servicios.</p>
@endsection
