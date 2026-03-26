<?php

namespace App\Models;

use App\Enums\SectionType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    /** @use HasFactory<\Database\Factories\HomepageSectionFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'type',
        'name',
        'sort_order',
        'is_active',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'config' => 'array',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
