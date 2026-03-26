<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements HasMedia
{
    use BelongsToTenant, HasFactory, HasSlug, HasTranslations, InteractsWithMedia, SoftDeletes;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'position',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
        'sku_prefix',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'position' => 'integer',
    ];

    // ═══════════════════════════════════════════
    // SLUG CONFIGURATION
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

    // ═══════════════════════════════════════════
    // MEDIA COLLECTIONS
    // ═══════════════════════════════════════════

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('banner')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('position');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('image');
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('banner');
    }

    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    public function getAllProductsCountAttribute(): int
    {
        $ids = $this->getAllDescendantIds();
        $ids[] = $this->id;

        return Product::whereIn('category_id', $ids)->count();
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function ancestors(): array
    {
        $ancestors = [];
        $category = $this->parent;

        while ($category) {
            array_unshift($ancestors, $category);
            $category = $category->parent;
        }

        return $ancestors;
    }

    public function getAllDescendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }

        return $ids;
    }

    public static function buildTree(?int $parentId = null): \Illuminate\Support\Collection
    {
        return static::where('parent_id', $parentId)
            ->active()
            ->ordered()
            ->with(['children' => function ($query) {
                $query->active()->ordered();
            }])
            ->get();
    }

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function isAncestorOf(Category $category): bool
    {
        return in_array($this->id, array_column($category->ancestors(), 'id'));
    }

    public function isDescendantOf(Category $category): bool
    {
        return in_array($category->id, array_column($this->ancestors(), 'id'));
    }
}
