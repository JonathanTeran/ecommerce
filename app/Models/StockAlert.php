<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_location_id',
        'type',
        'threshold',
        'current_quantity',
        'status',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class);
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', ['pending', 'acknowledged']);
    }

    public function acknowledge(?int $userId = null): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $userId ?? auth()->id(),
            'acknowledged_at' => now(),
        ]);
    }

    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'low_stock' => 'Stock Bajo',
            'out_of_stock' => 'Agotado',
            'overstock' => 'Sobrestock',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'acknowledged' => 'Reconocida',
            'resolved' => 'Resuelta',
            default => $this->status,
        };
    }
}
