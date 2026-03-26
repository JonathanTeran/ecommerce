<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Laravel\Scout\Searchable; // Enable when Scout is installed
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements HasMedia
{
    use BelongsToTenant, HasFactory, HasSlug, HasTranslations, InteractsWithMedia, SoftDeletes; // Searchable

    public $translatable = ['name', 'description', 'short_description', 'specifications', 'meta_title', 'meta_description'];

    protected $appends = ['formatted_price', 'primary_image_url', 'image_url'];

    protected $fillable = [
        'category_id',
        'brand_id',
        'sku',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'compare_price',
        'cost',
        'quantity',
        'low_stock_threshold',
        'weight',
        'dimensions',
        'specifications',
        'compatible_models',
        'warranty_months',
        'views_count',
        'sales_count',
        'is_active',
        'is_featured',
        'is_new',
        'requires_shipping',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'compatible_models' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'requires_shipping' => 'boolean',
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'warranty_months' => 'integer',
        'views_count' => 'integer',
        'sales_count' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->sku)) {
                $prefix = $product->category?->sku_prefix ?? 'PROD';
                $nextId = (static::max('id') ?? 0) + 1;
                $product->sku = $prefix.'-'.str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    // ═══════════════════════════════════════════
    // SLUG & SEARCH CONFIGURATION
    // ═══════════════════════════════════════════

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function searchableAs(): string
    {
        return 'products_tenant_'.($this->tenant_id ?? 0);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'category_name' => $this->category?->name,
            'brand_name' => $this->brand?->name,
            'compatible_models' => $this->compatible_models,
            'price' => (float) $this->price,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'is_new' => $this->is_new,
            'quantity' => $this->quantity,
            'average_rating' => $this->average_rating ?? 0,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_active;
    }

    // ═══════════════════════════════════════════
    // MEDIA COLLECTIONS
    // ═══════════════════════════════════════════

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(800)
            ->height(800)
            ->sharpen(10)
            ->nonQueued();
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->media();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class);
    }

    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'product_id',
            'related_product_id'
        );
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'price');
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeInCategory($query, $categoryId)
    {
        if (is_array($categoryId)) {
            return $query->whereIn('category_id', $categoryId);
        }

        return $query->where('category_id', $categoryId);
    }

    public function scopeInBrand($query, $brandId)
    {
        if (is_array($brandId)) {
            return $query->whereIn('brand_id', $brandId);
        }

        return $query->where('brand_id', $brandId);
    }

    public function scopePriceBetween($query, $min, $max)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }

        return $query;
    }

    public function scopePopular($query)
    {
        return $query->orderByDesc('sales_count');
    }

    public function scopeMostViewed($query)
    {
        return $query->orderByDesc('views_count');
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'medium') ?: null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->primary_image_url;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'thumb') ?: null;
    }

    public function getAllImagesAttribute(): array
    {
        return $this->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
                'large' => $media->getUrl('large'),
                'original' => $media->getUrl(),
            ];
        })->toArray();
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (! $this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function getSavingsAmountAttribute(): ?float
    {
        if (! $this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return $this->compare_price - $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->quantity > 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->low_stock_threshold;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }
        if ($this->quantity <= $this->low_stock_threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Agotado',
            'low_stock' => 'Últimas unidades',
            'in_stock' => 'En stock',
        };
    }

    public function getAverageRatingAttribute(): ?float
    {
        // Use preloaded withAvg if available, fallback to query
        if (array_key_exists('approved_reviews_avg_rating', $this->attributes)) {
            $avg = $this->attributes['approved_reviews_avg_rating'];

            return $avg ? round((float) $avg, 1) : null;
        }

        $avg = $this->reviews()->where('is_approved', true)->avg('rating');

        return $avg ? round($avg, 1) : null;
    }

    public function getReviewsCountAttribute(): int
    {
        // Use preloaded withCount if available, fallback to query
        if (array_key_exists('approved_reviews_count', $this->attributes)) {
            return (int) $this->attributes['approved_reviews_count'];
        }

        return $this->reviews()->where('is_approved', true)->count();
    }

    /**
     * Scope to eagerly load review stats and avoid N+1.
     */
    public function scopeWithReviewStats($query)
    {
        return $query
            ->withAvg(['reviews as approved_reviews_avg_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews as approved_reviews_count' => fn ($q) => $q->where('is_approved', true)]);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$'.number_format($this->price, 2);
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementSales(int $quantity = 1): void
    {
        $this->increment('sales_count', $quantity);
    }
}
