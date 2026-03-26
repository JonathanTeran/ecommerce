<?php

namespace App\Filament\Concerns;

use App\Enums\Module;

trait RequiresModule
{
    abstract protected static function requiredModule(): Module;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            return false;
        }

        return $tenant->hasModule(static::requiredModule());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
