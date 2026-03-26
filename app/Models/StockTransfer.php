<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_location_id',
        'to_location_id',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $transfer) {
            if (empty($transfer->transfer_number)) {
                $prefix = 'TRF';
                $next = static::withoutGlobalScopes()->max('id') ?? 0;
                $transfer->transfer_number = $prefix . '-' . str_pad($next + 1, 6, '0', STR_PAD_LEFT);
            }

            if (empty($transfer->created_by)) {
                $transfer->created_by = auth()->id();
            }
        });
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'to_location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Borrador',
            'in_transit' => 'En Tránsito',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            default => $this->status,
        };
    }
}
