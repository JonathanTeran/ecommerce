<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'options',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'options' => 'array',
    ];

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->price * $this->quantity);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$'.number_format($this->price, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$'.number_format($this->subtotal, 2);
    }

    public function getIsAvailableAttribute(): bool
    {
        $stockSource = $this->variant ?? $this->product;

        return $stockSource->is_active && $stockSource->quantity >= $this->quantity;
    }

    public function getMaxQuantityAttribute(): int
    {
        $stockSource = $this->variant ?? $this->product;

        return $stockSource->quantity;
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function incrementQuantity(int $amount = 1): void
    {
        $this->increment('quantity', $amount);
    }

    public function decrementQuantity(int $amount = 1): void
    {
        $newQuantity = max(1, $this->quantity - $amount);
        $this->update(['quantity' => $newQuantity]);
    }

    public function updatePrice(): void
    {
        $price = $this->variant?->price ?? $this->product->price;
        $this->update(['price' => $price]);
    }

    public function hasStockIssue(): bool
    {
        $stockSource = $this->variant ?? $this->product;

        return $stockSource->quantity < $this->quantity;
    }

    public function getStockMessage(): ?string
    {
        $stockSource = $this->variant ?? $this->product;

        if (! $stockSource->is_active) {
            return 'Este producto ya no está disponible';
        }

        if ($stockSource->quantity <= 0) {
            return 'Producto agotado';
        }

        if ($stockSource->quantity < $this->quantity) {
            return "Solo hay {$stockSource->quantity} unidades disponibles";
        }

        return null;
    }
}
