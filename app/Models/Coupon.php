<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type', // fixed, percentage, free_shipping
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'usage_limit_per_user',
        'starts_at',
        'expires_at',
        'is_active',
        'applicable_categories',
        'applicable_products',
        'excluded_products',
        'first_order_only',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
        'applicable_categories' => 'array',
        'applicable_products' => 'array',
        'excluded_products' => 'array',
    ];

    public function isValidFor(float $subtotal, ?int $userId = null): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }
        if ($this->min_order_amount && $subtotal < $this->min_order_amount) {
            return false;
        }

        if ($userId && $this->usage_limit_per_user) {
            $userUsage = Order::where('user_id', $userId)
                ->where('coupon_code', $this->code)
                ->count();

            if ($userUsage >= $this->usage_limit_per_user) {
                return false;
            }
        }

        if ($userId && $this->first_order_only) {
            $hasOrders = Order::where('user_id', $userId)->exists();

            if ($hasOrders) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount(float $subtotal, $items = null): float
    {
        if ($this->type === 'fixed') {
            return min($this->value, $subtotal);
        }

        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);
            if ($this->max_discount_amount) {
                return min($discount, $this->max_discount_amount);
            }

            return $discount;
        }

        // free_shipping logic handled elsewhere typically, or returns 0 discount on subtotal
        return 0;
    }
}
