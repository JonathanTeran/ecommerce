<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Attribute extends Model
{
    /** @use HasFactory<\Database\Factories\AttributeFactory> */
    use BelongsToTenant, HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'options',
        'is_filterable',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_filterable' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }
}
