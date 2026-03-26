<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FeatureFlag extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'key', 'is_enabled_globally', 'enabled_tenants', 'disabled_tenants'])
            ->logOnlyDirty()
            ->useLogName('feature-flag')
            ->setDescriptionForEvent(fn (string $eventName): string => "Feature flag {$eventName}: {$this->key}");
    }

    protected $fillable = [
        'name',
        'key',
        'description',
        'is_enabled_globally',
        'enabled_tenants',
        'disabled_tenants',
    ];

    protected $casts = [
        'is_enabled_globally' => 'boolean',
        'enabled_tenants' => 'array',
        'disabled_tenants' => 'array',
    ];

    protected static function booted(): void
    {
        static::observe(\App\Observers\FeatureFlagObserver::class);
    }
}
