<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyProgram extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'name',
        'points_per_dollar',
        'redemption_rate',
        'minimum_redemption_points',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'points_per_dollar' => 'decimal:2',
            'redemption_rate' => 'decimal:4',
            'minimum_redemption_points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function forCurrentTenant(): ?static
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        if (! $tenant) {
            return null;
        }

        return static::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->first();
    }
}
