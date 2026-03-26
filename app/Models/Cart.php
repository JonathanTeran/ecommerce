<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cart extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'coupon_id',
        'expires_at',
        'reminder_sent_at',
        'reminder_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'reminder_count' => 'integer',
    ];

    // ═══════════════════════════════════════════
    // BOOT
    // ═══════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cart) {
            if (empty($cart->session_id)) {
                $cart->session_id = Str::uuid()->toString();
            }
            if (empty($cart->expires_at)) {
                $cart->expires_at = now()->addDays(GeneralSetting::getCartExpirationDays());
            }
        });
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum('subtotal');
    }

    public function getDiscountAmountAttribute(): float
    {
        if (! $this->coupon) {
            return 0;
        }

        return $this->coupon->calculateDiscount($this->subtotal, $this->items);
    }

    public function getTaxAmountAttribute(): float
    {
        $taxableAmount = $this->subtotal - $this->discount_amount;
        $taxRate = cache()->remember(
            'tenant_' . ($this->tenant_id ?? 'global') . '_tax_rate',
            3600,
            fn () => GeneralSetting::first()?->tax_rate ?? 15.00
        );

        return $taxableAmount * ($taxRate / 100);
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal - $this->discount_amount + $this->tax_amount;
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$'.number_format($this->subtotal, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$'.number_format($this->total, 2);
    }

    public function getIsEmptyAttribute(): bool
    {
        return $this->items->isEmpty();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function addItem(Product $product, int $quantity = 1, ?ProductVariant $variant = null, array $options = []): CartItem
    {
        // Check if item already exists
        $existingItem = $this->items()
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->first();

        if ($existingItem) {
            $existingItem->incrementQuantity($quantity);

            return $existingItem->fresh();
        }

        $price = $variant?->price ?? $product->price;

        return $this->items()->create([
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
            'price' => $price,
            'options' => $options,
        ]);
    }

    public function updateItem(int $itemId, int $quantity): ?CartItem
    {
        $item = $this->items()->find($itemId);

        if (! $item) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();

            return null;
        }

        $item->update(['quantity' => $quantity]);

        return $item->fresh();
    }

    public function removeItem(int $itemId): bool
    {
        return $this->items()->where('id', $itemId)->delete() > 0;
    }

    public function clear(): void
    {
        $this->items()->delete();
        $this->update(['coupon_id' => null]);
    }

    public function applyCoupon(Coupon $coupon): bool
    {
        if (! $coupon->isValidFor($this->subtotal, $this->user_id)) {
            return false;
        }

        $this->update(['coupon_id' => $coupon->id]);

        return true;
    }

    public function removeCoupon(): void
    {
        $this->update(['coupon_id' => null]);
    }

    public function syncPrices(): void
    {
        foreach ($this->items as $item) {
            $item->updatePrice();
        }
    }

    public function validateStock(): array
    {
        $issues = [];

        foreach ($this->items as $item) {
            if ($item->hasStockIssue()) {
                $issues[] = [
                    'item_id' => $item->id,
                    'product_name' => $item->product->name,
                    'requested' => $item->quantity,
                    'available' => $item->product->quantity,
                    'message' => $item->getStockMessage(),
                ];
            }
        }

        return $issues;
    }

    public function canCheckout(): bool
    {
        if ($this->is_empty || $this->is_expired) {
            return false;
        }

        return empty($this->validateStock());
    }

    public function mergeWith(Cart $otherCart): void
    {
        foreach ($otherCart->items as $item) {
            $this->addItem(
                $item->product,
                $item->quantity,
                $item->variant,
                $item->options ?? []
            );
        }

        $otherCart->delete();
    }

    public function assignToUser(User $user): void
    {
        // Check if user already has a cart
        $existingCart = Cart::forUser($user->id)->first();

        if ($existingCart && $existingCart->id !== $this->id) {
            $this->mergeWith($existingCart);
        }

        $this->update(['user_id' => $user->id]);
    }

    public function extendExpiration(int $days = 30): void
    {
        $this->update(['expires_at' => now()->addDays($days)]);
    }

    public function toCheckoutData(): array
    {
        return [
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'name' => $item->product->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'options' => $item->options,
                    'image_url' => $item->product->image_url,
                ];
            })->toArray(),
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'coupon_code' => $this->coupon?->code,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
        ];
    }
}
