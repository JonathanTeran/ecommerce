<?php

namespace App\Http\Controllers;

use App\Enums\SectionType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::active()->ordered()->get();

        $sectionData = $this->loadSectionData($sections);

        $sectionFonts = $sections
            ->map(fn ($s) => $s->config['style']['font_family'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $settings = \App\Models\GeneralSetting::cached();
        $seoConfig = $settings?->getSeoConfig() ?? [];

        $seo = [
            'title' => !empty($seoConfig['home_meta_title']) ? $seoConfig['home_meta_title'] : ($settings?->site_name ?? config('app.name')),
            'metaDescription' => $seoConfig['home_meta_description'] ?? '',
        ];

        return view('welcome', compact('sections', 'sectionData', 'sectionFonts', 'seo'));
    }

    /** @return array<int, array<string, mixed>> */
    private function loadSectionData(Collection $sections): array
    {
        $data = [];

        foreach ($sections as $section) {
            $data[$section->id] = match ($section->type) {
                SectionType::CategoryStrip => $this->loadCategoryStripData($section),
                SectionType::ProductGrid => $this->loadProductGridData($section),
                SectionType::BrandSlider => $this->loadBrandSliderData(),
                default => [],
            };
        }

        return $data;
    }

    /** @return array{categories: Collection} */
    private function loadCategoryStripData(HomepageSection $section): array
    {
        $categoryIds = collect($section->config['categories'] ?? [])
            ->pluck('category_id')
            ->filter();

        if ($categoryIds->isEmpty()) {
            $categories = Category::active()
                ->featured()
                ->ordered()
                ->with('media')
                ->take(6)
                ->get();
        } else {
            $categories = Category::whereIn('id', $categoryIds)
                ->with('media')
                ->get();
        }

        return ['categories' => $categories];
    }

    /** @return array{products: Collection} */
    private function loadProductGridData(HomepageSection $section): array
    {
        $config = $section->config;
        $limit = $config['limit'] ?? 8;
        $query = Product::where('is_active', true)->with(['media', 'category', 'brand']);

        $products = match ($config['source'] ?? 'trending') {
            'featured' => $query->where('is_featured', true)->take($limit)->get(),
            'new' => $query->latest()->take($limit)->get(),
            'category' => $query->where('category_id', $config['category_id'] ?? 0)->take($limit)->get(),
            default => $query->orderByDesc('views_count')->take($limit)->get(),
        };

        return [
            'products' => $products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'image_url' => $product->primary_image_url,
                'url' => route('products.show', $product),
                'category_name' => $product->category?->name,
                'formatted_price' => $product->formatted_price,
            ]),
        ];
    }

    /** @return array{brands: Collection} */
    private function loadBrandSliderData(): array
    {
        return [
            'brands' => Brand::active()->ordered()->with('media')->get(),
        ];
    }
}
