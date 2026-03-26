<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStorefrontTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->bound('current_tenant') && app('current_tenant') !== null) {
            return $next($request);
        }

        $host = $request->getHost();

        $tenant = Tenant::where('domain', $host)
            ->orWhere(fn ($q) => $q->where('is_default', true))
            ->orderByRaw('CASE WHEN domain = ? THEN 0 ELSE 1 END', [$host])
            ->first();

        if ($tenant) {
            if (! $tenant->is_active) {
                return response()->view('errors.tenant-suspended', [
                    'tenant' => $tenant,
                    'message' => $tenant->suspension_message,
                ], 503);
            }

            app()->instance('current_tenant', $tenant);
        }

        return $next($request);
    }
}
