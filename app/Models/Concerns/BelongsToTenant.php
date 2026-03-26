<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public function initializeBelongsToTenant(): void
    {
        $this->mergeFillable(['tenant_id']);
    }

    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = auth()->user();

            if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return;
            }

            $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

            if ($tenant) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenant->id);
            } elseif (! app()->runningInConsole() && ! app()->runningUnitTests()) {
                // Strict mode: if no tenant and not CLI/test, scope to impossible value
                $builder->where($builder->getModel()->getTable() . '.tenant_id', 0);
            }
        });

        static::creating(function (Model $model) {
            if (! $model->tenant_id) {
                $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

                if ($tenant) {
                    $model->tenant_id = $tenant->id;
                }
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
