<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderShippedMail;
use App\Models\Concerns\BelongsToTenant;
use App\Notifications\OrderStatusChanged;
use App\Services\TenantMailService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'internal_number',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'discount_amount',
        'coupon_code',
        'tax_amount',
        'shipping_amount',
        'total',
        'currency',
        'notes',
        'admin_notes',
        'billing_address',
        'shipping_address',
        'shipping_method',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'ip_address',
        'user_agent',
        'payment_proof_path',
        'surcharge_amount',
        'sri_authorization_status',
        'sri_access_key',
        'sri_xml_path',
        'sri_authorized_xml_path',
        'sri_authorization_date',
        'sri_authorization_number',
        'sri_error_message',
        'legal_acceptance',
        'placed_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'legal_acceptance' => 'array',
        'placed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════
    // BOOT
    // ═══════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }

            // Auto-assign sequential internal number per tenant
            if (empty($order->internal_number) && $order->tenant_id) {
                $order->internal_number = static::withoutGlobalScopes()
                    ->where('tenant_id', $order->tenant_id)
                    ->max('internal_number') + 1;
            }
        });

        static::saving(function ($order) {
            // When saving, ensure totals are consistent if items are available
            // Note: If items are not loaded or being saved separately, this might use stale usage.
            // But for simple updates like shipping_amount, this works great.
            // We use 'items' relation which might query DB.

            // Limit this to when not already calculating to avoid side effects if necessary,
            // but setting attributes on 'saving' is standard.

            // To be safe, we only recalculate if we are not just touching timestamps
            if ($order->isDirty(['shipping_amount', 'discount_amount', 'subtotal'])) {
                // We can re-sum totals.
                // However, calculateTotals uses items().
                // If we are just updating shipping, items might not be loaded.

                // If subtotal is NOT dirty, we trust it or we assume logic elsewhere handled it.
                // If shipping_amount IS dirty, we need to update Total.

                // Strategy: Trust 'subtotal' if it exists, otherwise recalc from items?
                // For now, let's just create a simpler recalculation that trusts current subtotal
                // unless we explicitly want to sum items.

                $subtotal = $order->subtotal ?? 0;
                $taxRate = GeneralSetting::getTaxRateDecimal();
                $taxAmount = $subtotal * $taxRate;
                $shipping = $order->shipping_amount ?? 0;
                $discount = $order->discount_amount ?? 0;
                $surcharge = $order->surcharge_amount ?? 0;

                $order->tax_amount = $taxAmount;
                $order->total = $subtotal - $discount + $taxAmount + $shipping + $surcharge;
            }
        });
    }

    /**
     * Formatted internal number for display (e.g. #0001)
     */
    public function getDisplayNumberAttribute(): string
    {
        if ($this->internal_number) {
            return '#'.str_pad($this->internal_number, 4, '0', STR_PAD_LEFT);
        }

        return $this->order_number;
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'CP';
        $date = now()->format('ymd');

        // Use random suffix to avoid race condition on sequence
        return sprintf('%s-%s-%s', $prefix, $date, strtoupper(\Illuminate\Support\Str::random(6)));
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
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderByDesc('created_at');
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ProductReturn::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeByStatus($query, OrderStatus|array $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }

        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->whereIn('status', [
            OrderStatus::CONFIRMED,
            OrderStatus::PROCESSING,
        ]);
    }

    public function scopeShipped($query)
    {
        return $query->where('status', OrderStatus::SHIPPED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', OrderStatus::DELIVERED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', OrderStatus::CANCELLED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', PaymentStatus::COMPLETED);
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$'.number_format($this->total, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$'.number_format($this->subtotal, 2);
    }

    public function getShippingNameAttribute(): ?string
    {
        return $this->shipping_address['name'] ?? null;
    }

    public function getShippingFullNameAttribute(): ?string
    {
        return $this->shipping_address['name'] ?? null;
    }

    public function getShippingFullAddressAttribute(): ?string
    {
        $address = $this->shipping_address;
        if (! $address) {
            return null;
        }

        $parts = array_filter([
            $address['address'] ?? null,
            $address['city'] ?? null,
            $address['state'] ?? null,
            $address['zip'] ?? null,
        ]);

        return implode(', ', $parts);
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::CONFIRMED,
            OrderStatus::ON_HOLD,
        ]);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === PaymentStatus::COMPLETED;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === OrderStatus::DELIVERED;
    }

    public function getTrackingUrlAttribute(): ?string
    {
        if (! $this->tracking_number) {
            return null;
        }

        // Servientrega tracking URL
        return "https://www.servientrega.com.ec/rastreo/{$this->tracking_number}";
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function updateStatus(OrderStatus $newStatus, ?string $notes = null, ?User $user = null): bool
    {
        if (! $this->status->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $this->status;

        $this->update(['status' => $newStatus]);

        // Record history
        $this->statusHistory()->create([
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
            'notes' => $notes,
            'user_id' => $user?->id,
        ]);

        // Update timestamps based on status
        if ($newStatus === OrderStatus::SHIPPED && ! $this->shipped_at) {
            $this->update(['shipped_at' => now()]);
        } elseif ($newStatus === OrderStatus::DELIVERED && ! $this->delivered_at) {
            $this->update(['delivered_at' => now()]);
        } elseif ($newStatus === OrderStatus::CANCELLED && ! $this->cancelled_at) {
            $this->update(['cancelled_at' => now()]);
        }

        // Send status change email notifications
        $this->sendStatusChangeEmail($newStatus);

        // Send in-app notification
        if ($this->user) {
            $this->user->notify(new OrderStatusChanged($this, $oldStatus, $newStatus));
        }

        return true;
    }

    public function cancel(string $reason, ?User $user = null): bool
    {
        if (! $this->can_be_cancelled) {
            return false;
        }

        $this->update(['cancellation_reason' => $reason]);

        return $this->updateStatus(OrderStatus::CANCELLED, $reason, $user);
    }

    public function markAsPaid(): void
    {
        $this->update(['payment_status' => PaymentStatus::COMPLETED]);
    }

    public function calculateTotals(): void
    {
        $this->setAttributesFromTotals();
        $this->save();
    }

    public function setAttributesFromTotals(): void
    {
        // Recalculate based on current items relationship or loaded items
        // Note: accessing $this->items might trigger a fresh query if not loaded.
        // For 'saving' event, we might need to be careful if items are being saved simultaneously.
        // But for shipping_update standalone, this is fine.

        $subtotal = $this->items->sum('subtotal');
        $taxRate = GeneralSetting::getTaxRateDecimal();
        $taxAmount = $subtotal * $taxRate;

        // Ensure properties are numbers
        $shipping = $this->shipping_amount ?? 0;
        $discount = $this->discount_amount ?? 0;
        $surcharge = $this->surcharge_amount ?? 0;

        $total = $subtotal - $discount + $taxAmount + $shipping + $surcharge;

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total = $total;
    }

    public function addItem(Product $product, int $quantity = 1, ?ProductVariant $variant = null, array $options = []): OrderItem
    {
        return $this->items()->create([
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'name' => $product->name,
            'sku' => $variant?->sku ?? $product->sku,
            'price' => $variant?->price ?? $product->price,
            'quantity' => $quantity,
            'subtotal' => ($variant?->price ?? $product->price) * $quantity,
            'options' => $options,
        ]);
    }

    public function setShippingAddress(Address $address): void
    {
        $this->update([
            'shipping_address' => $address->toArray(),
        ]);
    }

    public function setBillingAddress(Address $address): void
    {
        $this->update([
            'billing_address' => $address->toArray(),
        ]);
    }

    private function sendStatusChangeEmail(OrderStatus $status): void
    {
        if (! $this->user) {
            return;
        }

        $mail = match ($status) {
            OrderStatus::SHIPPED => new OrderShippedMail($this),
            OrderStatus::DELIVERED => new OrderDeliveredMail($this),
            OrderStatus::CANCELLED => new OrderCancelledMail($this),
            default => null,
        };

        if ($mail) {
            app(TenantMailService::class)->send($mail);
        }
    }
}
