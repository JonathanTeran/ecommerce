<?php

namespace App\Http\Middleware;

use App\Enums\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            abort(403, 'No se encontro un tenant activo.');
        }

        $moduleEnum = Module::tryFrom($module);

        if (! $moduleEnum) {
            abort(500, "Modulo desconocido: {$module}");
        }

        if (! $tenant->hasModule($moduleEnum)) {
            abort(403, "Tu plan actual no incluye el modulo: {$moduleEnum->label()}. Actualiza tu plan para acceder.");
        }

        return $next($request);
    }
}
