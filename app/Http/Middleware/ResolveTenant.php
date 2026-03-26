<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            $tenant = $user->tenant;

            if (! $tenant || ! $tenant->is_active) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->view('errors.tenant-suspended', [
                    'tenant' => $tenant,
                    'message' => $tenant?->suspension_message,
                ], 403);
            }

            app()->instance('current_tenant', $tenant);
        }

        return $next($request);
    }
}
