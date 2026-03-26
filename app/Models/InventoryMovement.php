<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'balance_quantity',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
        'warehouse_location_id',
        'batch_number',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function warehouseLocation()
    {
        return $this->belongsTo(WarehouseLocation::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'initial_balance' => 'Saldo Inicial',
            'purchase' => 'Compra (Ingreso)',
            'sale' => 'Venta (Egreso)',
            'return' => 'Devolución',
            'adjustment_inc' => 'Ajuste (+)',
            'adjustment_dec' => 'Ajuste (-)',
            'transfer_out' => 'Transferencia (Salida)',
            'transfer_in' => 'Transferencia (Entrada)',
            default => $this->type,
        };
    }
}
