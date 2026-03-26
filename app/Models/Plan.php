<?php

namespace App\Models;

use App\Enums\Module;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'price',
        'billing_period',
        'max_products',
        'max_users',
        'modules',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => PlanType::class,
            'price' => 'decimal:2',
            'max_products' => 'integer',
            'max_users' => 'integer',
            'modules' => 'array',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // ═══════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════

    public function hasModule(Module $module): bool
    {
        return in_array($module->value, $this->modules ?? []);
    }

    /**
     * @return Module[]
     */
    public function getModuleEnums(): array
    {
        return array_filter(
            array_map(fn (string $value) => Module::tryFrom($value), $this->modules ?? [])
        );
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }
}
