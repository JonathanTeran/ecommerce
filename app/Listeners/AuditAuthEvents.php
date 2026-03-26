<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AuditAuthEvents
{
    public function handleLogin(Login $event): void
    {
        $user = $event->user;

        activity('auth')
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard' => $event->guard,
            ])
            ->log("Inicio de sesion: {$user->name} ({$user->email})");

        if (method_exists($user, 'updateLastLogin')) {
            $user->updateLastLogin();
        }
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        $user = $event->user;

        activity('auth')
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
            ])
            ->log("Cierre de sesion: {$user->name}");
    }
}
