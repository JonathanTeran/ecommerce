<?php

namespace App\Http\Controllers;

use App\Enums\SectionType;
use App\Enums\ThemeTemplate;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\StoreTemplate;
use Illuminate\Http\Request;

class StorefrontPreviewController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Enlace de vista previa expirado o inválido.');
        }

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            abort(404);
        }

        // Override tenant theme in memory only (no DB writes)
        if ($request->filled('theme_template')) {
            $template = ThemeTemplate::tryFrom($request->query('theme_template'));
            if ($template) {
                $tenant->theme_template = $template;
                $tenant->store_template_id = null;
            }
        }

        if ($request->filled('store_template_id')) {
            $storeTemplate = StoreTemplate::find($request->query('store_template_id'));
            if ($storeTemplate) {
                $tenant->store_template_id = $storeTemplate->id;
                $tenant->setRelation('storeTemplate', $storeTemplate);
            }
        }

        // Render default storefront with theme override
        $sections = HomepageSection::active()->ordered()->get();
        $sectionData = $this->loadSectionData($sections);

        $sectionFonts = $sections
            ->map(fn ($s) => $s->config['style']['font_family'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $seo = [
            'title' => 'Vista Previa — '.($tenant->name ?? config('app.name')),
            'metaDescription' => '',
        ];

        $isPreview = true;

        return view('welcome', compact('sections', 'sectionData', 'sectionFonts', 'seo', 'tenant', 'isPreview'));
    }

    private function loadSectionData($sections): array
    {
        $data = [];

        foreach ($sections as $section) {
            $data[$section->id] = match ($section->type) {
                SectionType::CategoryStrip => $this->loadCategoryData($section),
                SectionType::ProductGrid => $this->loadProductData($section),
                SectionType::BrandSlider => ['brands' => Brand::query()->take(20)->get()],
                default => [],
            };
        }

        return $data;
    }

    private function loadCategoryData($section): array
    {
        $configCategories = $section->config['categories'] ?? [];

        if (! empty($configCategories)) {
            $categories = Category::whereIn('id', $configCategories)->withCount('products')->get();
        } else {
            $categories = Category::whereNull('parent_id')
                ->where('is_active', true)
                ->withCount('products')
                ->orderBy('name')
                ->take(8)
                ->get();
        }

        return ['categories' => $categories];
    }

    private function loadProductData($section): array
    {
        $config = $section->config;
        $limit = $config['limit'] ?? 8;
        $source = $config['source'] ?? 'trending';

        $query = Product::where('is_active', true)->with(['media', 'brand', 'category', 'reviews']);

        $products = match ($source) {
            'featured' => $query->where('is_featured', true)->latest()->take($limit)->get(),
            'new' => $query->where('is_new', true)->latest()->take($limit)->get(),
            default => $query->latest()->take($limit)->get(),
        };

        return [
            'products' => $products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'image_url' => $product->primary_image_url,
                'url' => route('products.show', $product),
                'brand_name' => $product->brand?->name,
                'formatted_price' => $product->formatted_price,
                'price' => $product->price,
                'compare_price' => $product->compare_price,
                'formatted_compare_price' => $product->compare_price ? '$'.number_format($product->compare_price, 2) : null,
                'discount_percent' => $product->compare_price && $product->compare_price > $product->price
                    ? round((($product->compare_price - $product->price) / $product->compare_price) * 100)
                    : null,
                'is_new' => (bool) $product->is_new,
                'is_featured' => (bool) $product->is_featured,
                'rating' => round($product->reviews()->avg('rating') ?? 0, 1),
                'reviews_count' => $product->reviews()->count(),
                'in_stock' => $product->quantity > 0,
                'low_stock' => $product->quantity > 0 && $product->quantity <= ($product->low_stock_threshold ?? 5),
            ])->toArray(),
        ];
    }
}
