<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'preview_image',
        'preview_desktop',
        'preview_mobile',
        'assets_path',
        'css_file',
        'color_scheme',
        'fonts',
        'features',
        'is_active',
        'is_premium',
        'sort_order',
    ];

    protected $casts = [
        'color_scheme' => 'array',
        'fonts' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getColorSwatchesAttribute(): array
    {
        return array_values($this->color_scheme ?? []);
    }

    public function getDemoUrlAttribute(): ?string
    {
        $demoPages = [
            'anton' => '/templates/anton-demo/Home_01.html',
            'jovenca' => '/templates/jovenca-demo/home.html',
            'radios' => '/templates/radios-demo/index.html',
        ];

        return $demoPages[$this->slug] ?? null;
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'fashion' => 'Moda & Ropa',
            'jewelry' => 'Joyeria & Accesorios',
            'electronics' => 'Electronica & Tech',
            'general' => 'General',
            'food' => 'Alimentos & Bebidas',
            'beauty' => 'Belleza & Cuidado',
            default => ucfirst($this->category),
        };
    }
}
