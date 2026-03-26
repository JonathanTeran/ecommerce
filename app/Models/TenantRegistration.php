<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantRegistration extends Model
{
    protected $fillable = [
        'store_name',
        'slug',
        'plan_id',
        'owner_name',
        'owner_email',
        'owner_phone',
        'password',
        'country',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'email_verified_at',
        'verification_token',
        'provisioned_tenant_id',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'reviewed_at' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function provisionedTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'provisioned_tenant_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }
}
