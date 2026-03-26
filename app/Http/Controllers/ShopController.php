<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->where('is_active', true)->with(['brand', 'media'])->withReviewStats();

        // Filter by Category (include all descendant subcategories)
        if ($request->has('category')) {
            $category = Category::where('slug', $request->category)->first();

            if ($category) {
                $categoryIds = $category->getAllDescendantIds();
                $categoryIds[] = $category->id;
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Filter by Brand
        if ($request->has('brand')) {
            $query->whereHas('brand', function ($q) use ($request) {
                $q->where('slug', $request->brand);
            });
        }

        // Filter by Price
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search
        if ($request->has('q')) {
            $qTerm = $request->q;
            $qTermLower = mb_strtolower($qTerm);

            $query->where(function ($q) use ($qTermLower) {
                $q->whereRaw('LOWER(CAST(name AS CHAR)) LIKE ?', ["%{$qTermLower}%"])
                    ->orWhereRaw('LOWER(CAST(description AS CHAR)) LIKE ?', ["%{$qTermLower}%"])
                    ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$qTermLower}%"])
                    ->orWhereRaw('LOWER(CAST(short_description AS CHAR)) LIKE ?', ["%{$qTermLower}%"]);
            });

            // Track search history in session
            $searchHistory = $request->session()->get('search_history', []);
            $searchHistory = array_filter($searchHistory, fn (string $term): bool => $term !== $qTerm);
            array_unshift($searchHistory, $qTerm);
            $searchHistory = array_slice($searchHistory, 0, 10);
            $request->session()->put('search_history', $searchHistory);
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->get();
        $brands = Brand::where('is_active', true)->get();
        $banners = \App\Models\Banner::active()->orderBy('sort_order')->get();

        $title = __('Tienda');
        if ($request->has('category')) {
            $activeCategory = Category::where('slug', $request->category)->first();
            $title = $activeCategory?->meta_title ?: ($activeCategory?->name ?: __('Tienda'));
        }
        $metaDescription = __('Explora nuestro catálogo de productos.');
        $searchHistory = $request->session()->get('search_history', []);

        return view('shop.index', compact('products', 'categories', 'brands', 'banners', 'title', 'metaDescription', 'searchHistory'));
    }
}
