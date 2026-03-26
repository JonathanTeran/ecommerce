<?php

namespace App\Models;

use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReturn extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $table = 'returns';

    protected $fillable = [
        'order_id',
        'user_id',
        'return_number',
        'status',
        'reason',
        'description',
        'resolution_type',
        'refund_amount',
        'admin_notes',
        'approved_at',
        'received_at',
        'refunded_at',
        'closed_at',
    ];

    protected $casts = [
        'status' => ReturnStatus::class,
        'reason' => ReturnReason::class,
        'refund_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
        'refunded_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $return) {
            if (empty($return->return_number)) {
                $return->return_number = static::generateReturnNumber();
            }
        });
    }

    public static function generateReturnNumber(): string
    {
        $prefix = 'RMA';
        $date = now()->format('ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function scopeByStatus($query, ReturnStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', ReturnStatus::Requested);
    }

    public function updateStatus(ReturnStatus $newStatus, ?string $notes = null): bool
    {
        if (! $this->status->canTransitionTo($newStatus)) {
            return false;
        }

        $this->update([
            'status' => $newStatus,
            'admin_notes' => $notes ?? $this->admin_notes,
        ]);

        if ($newStatus === ReturnStatus::Approved) {
            $this->update(['approved_at' => now()]);
        } elseif ($newStatus === ReturnStatus::ItemReceived) {
            $this->update(['received_at' => now()]);
        } elseif ($newStatus === ReturnStatus::Refunded) {
            $this->update(['refunded_at' => now()]);
        } elseif ($newStatus === ReturnStatus::Closed) {
            $this->update(['closed_at' => now()]);
        }

        return true;
    }
}
