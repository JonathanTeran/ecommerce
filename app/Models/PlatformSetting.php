<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PlatformSetting extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('platform')
            ->setDescriptionForEvent(fn (string $eventName): string => "Configuracion de plataforma {$eventName}");
    }

    protected $fillable = [
        'brand_name',
        'brand_logo',
        'support_email',
        'support_phone',
        'terms_of_service_url',
        'privacy_policy_url',
        'allow_new_registrations',
    ];

    protected $casts = [
        'allow_new_registrations' => 'boolean',
    ];
}
