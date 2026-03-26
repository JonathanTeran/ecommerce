<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalIntegration extends Model
{
    protected $fillable = [
        'name',
        'provider',
        'type',
        'logo',
        'is_active',
        'requires_setup',
        'description',
        'global_credentials',
        'supported_countries',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_setup' => 'boolean',
        'global_credentials' => 'array',
        'supported_countries' => 'array',
    ];
}
