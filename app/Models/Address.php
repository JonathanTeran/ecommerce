<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'user_id',
        'type', // billing, shipping
        'label',
        'first_name',
        'last_name',
        'cedula',
        'company',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
        'country',
        'phone',
        'delivery_instructions',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->province,
            $this->country,
        ]));
    }
}
