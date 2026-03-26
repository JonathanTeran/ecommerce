<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataExportRequest extends Model
{
    protected $fillable = [
        'tenant_id',
        'requested_by',
        'type',
        'status',
        'reason',
        'notes',
        'file_path',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }
}
