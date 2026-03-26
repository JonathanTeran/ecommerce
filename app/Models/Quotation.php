<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Notifications\QuotationStatusChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'quotation_number',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'currency',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'customer_notes',
        'admin_notes',
        'shipping_address',
        'billing_address',
        'valid_until',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'converted_order_id',
        'converted_at',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'valid_until' => 'date',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'converted_at' => 'datetime',
            'placed_at' => 'datetime',
        ];
    }

    // ═══════════════════════════════════════════
    // BOOT
    // ═══════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = static::generateQuotationNumber();
            }
            if (empty($quotation->valid_until)) {
                $quotation->valid_until = now()->addDays(GeneralSetting::getQuotationValidityDays());
            }
        });
    }

    public static function generateQuotationNumber(): string
    {
        $prefix = GeneralSetting::getQuotationPrefix();
        $date = now()->format('ymd');

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
        return $this->hasMany(QuotationItem::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopePending($query)
    {
        return $query->where('status', QuotationStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', QuotationStatus::Approved);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            QuotationStatus::Rejected,
            QuotationStatus::Expired,
            QuotationStatus::Converted,
        ])->where(function ($q) {
            $q->whereNull('valid_until')
                ->orWhere('valid_until', '>=', today());
        });
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function getIsConvertibleAttribute(): bool
    {
        return $this->status === QuotationStatus::Approved
            && ! $this->is_expired
            && ! $this->converted_order_id;
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function approve(User $admin): void
    {
        if (! $this->status->canTransitionTo(QuotationStatus::Approved)) {
            throw new \RuntimeException("No se puede aprobar una cotización con estado: {$this->status->getLabel()}");
        }

        $oldStatus = $this->status;

        $this->update([
            'status' => QuotationStatus::Approved,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        if ($this->user) {
            $this->user->notify(new QuotationStatusChanged($this, $oldStatus, QuotationStatus::Approved));
        }
    }

    public function reject(User $admin, string $reason): void
    {
        if (! $this->status->canTransitionTo(QuotationStatus::Rejected)) {
            throw new \RuntimeException("No se puede rechazar una cotización con estado: {$this->status->getLabel()}");
        }

        $oldStatus = $this->status;

        $this->update([
            'status' => QuotationStatus::Rejected,
            'rejected_by' => $admin->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        if ($this->user) {
            $this->user->notify(new QuotationStatusChanged($this, $oldStatus, QuotationStatus::Rejected));
        }
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $taxRate = GeneralSetting::first()?->tax_rate ?? 15.00;
        $discount = $this->discount_amount ?? 0;
        $taxableAmount = $subtotal - $discount;
        $taxAmount = $taxableAmount * ($taxRate / 100);
        $total = $subtotal - $discount + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }
}
