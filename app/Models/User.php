<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasMedia, HasName
{
    use HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, LogsActivity, Notifiable, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active', 'tenant_id', 'sub_role'])
            ->logOnlyDirty()
            ->useLogName('user')
            ->setDescriptionForEvent(fn (string $eventName): string => "Usuario {$eventName}: {$this->name}");
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'sub_role',
        'tenant_id',
        'phone',
        'identification_type',
        'identification_number',
        'date_of_birth',
        'gender',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'preferences',
        'newsletter_subscribed',
        'points_balance',
        'billing_address',
        'billing_city',
        'legal_acceptance',
        'social_provider',
        'social_id',
        'social_avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'preferences' => 'array',
        'newsletter_subscribed' => 'boolean',
        'legal_acceptance' => 'array',
    ];

    // ═══════════════════════════════════════════
    // MEDIA COLLECTIONS
    // ═══════════════════════════════════════════

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultShippingAddress(): HasOne
    {
        return $this->hasOne(Address::class)
            ->where('type', 'shipping')
            ->where('is_default', true);
    }

    public function defaultBillingAddress(): HasOne
    {
        return $this->hasOne(Address::class)
            ->where('type', 'billing')
            ->where('is_default', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ProductReturn::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeSubscribers($query)
    {
        return $query->where('newsletter_subscribed', true);
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('avatar') ?: $this->default_avatar;
    }

    public function getFilamentName(): string
    {
        return "{$this->name} ({$this->email})";
    }

    public function getDefaultAvatarAttribute(): string
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=random';
    }

    public function getFirstNameAttribute(): string
    {
        return explode(' ', $this->name)[0];
    }

    public function getLastNameAttribute(): string
    {
        $parts = explode(' ', $this->name);

        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }

    public function getTotalOrdersAttribute(): int
    {
        return $this->orders()->count();
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->orders()
            ->where('payment_status', 'completed')
            ->sum('total');
    }

    public function getFormattedTotalSpentAttribute(): string
    {
        return '$'.number_format($this->total_spent, 2);
    }

    public function getWishlistCountAttribute(): int
    {
        return $this->wishlists()->count();
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function updateLastLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    public function getOrCreateCart(): Cart
    {
        return $this->cart ?? Cart::create(['user_id' => $this->id]);
    }

    public function addToWishlist(Product $product): Wishlist
    {
        return $this->wishlists()->firstOrCreate([
            'product_id' => $product->id,
        ]);
    }

    public function removeFromWishlist(Product $product): bool
    {
        return $this->wishlists()
            ->where('product_id', $product->id)
            ->delete() > 0;
    }

    public function hasInWishlist(Product $product): bool
    {
        return $this->wishlists()
            ->where('product_id', $product->id)
            ->exists();
    }

    public function setDefaultAddress(Address $address): void
    {
        // Remove default from same type addresses
        $this->addresses()
            ->where('type', $address->type)
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);
    }

    public function canPlaceOrder(): bool
    {
        return $this->is_active && $this->email_verified_at !== null;
    }

    public function getPreference(string $key, $default = null)
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference(string $key, $value): void
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->update(['preferences' => $preferences]);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer') || ! $this->hasAnyRole(['admin', 'staff', 'super_admin']);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'super-admin') {
            return $this->isSuperAdmin();
        }

        if ($panel->getId() === 'admin') {
            return $this->isAdmin() && $this->email_verified_at && $this->tenant_id;
        }

        return true;
    }

    // ═══════════════════════════════════════════
    // TENANT
    // ═══════════════════════════════════════════

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
