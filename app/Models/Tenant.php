<?php

namespace App\Models;

use App\Enums\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tenant extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'domain', 'is_active', 'is_demo', 'theme_color', 'country', 'currency'])
            ->logOnlyDirty()
            ->useLogName('tenant')
            ->setDescriptionForEvent(fn (string $eventName): string => "Tenant {$eventName}: {$this->name}");
    }

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo',
        'favicon',
        'theme_color',
        'country',
        'currency',
        'language',
        'timezone',
        'is_active',
        'is_demo',
        'is_default',
        'suspension_message',
        'settings',
        'trial_ends_at',
        'deactivated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_demo' => 'boolean',
        'is_default' => 'boolean',
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name);
            }
        });
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->latestOfMany();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function generalSettings(): HasOne
    {
        return $this->hasOne(GeneralSetting::class);
    }

    // ═══════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════

    public function currentPlan(): ?Plan
    {
        return $this->activeSubscription?->plan;
    }

    public function hasModule(Module $module): bool
    {
        $plan = $this->currentPlan();

        if (! $plan) {
            return false;
        }

        return $plan->hasModule($module);
    }

    public function canAddProduct(): bool
    {
        $plan = $this->currentPlan();

        if (! $plan || ! $plan->max_products) {
            return true;
        }

        return $this->products()->count() < $plan->max_products;
    }

    public function canAddUser(): bool
    {
        $plan = $this->currentPlan();

        if (! $plan || ! $plan->max_users) {
            return true;
        }

        return $this->users()->count() < $plan->max_users;
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function deactivate(): void
    {
        $this->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);
    }

    public function reactivate(): void
    {
        $this->update([
            'is_active' => true,
            'deactivated_at' => null,
        ]);
    }

    public function isDeactivated(): bool
    {
        return ! $this->is_active && $this->deactivated_at !== null;
    }

    public function isPendingPurge(int $months = 3): bool
    {
        return $this->isDeactivated()
            && $this->deactivated_at->addMonths($months)->isPast();
    }
}
