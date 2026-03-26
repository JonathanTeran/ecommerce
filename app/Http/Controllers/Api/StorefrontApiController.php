<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Catálogo', description: 'Búsqueda, categorías y productos del storefront')]
class StorefrontApiController extends Controller
{
    #[OA\Get(
        path: '/search',
        summary: 'Buscar productos',
        description: 'Busca productos por nombre, descripción o SKU. Usa Meilisearch si está configurado, con fallback a LIKE.',
        tags: ['Catálogo'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, description: 'Término de búsqueda', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista de productos encontrados', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductSearchItem'))),
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');

        if (! $query) {
            return response()->json([]);
        }

        // Use Scout search if available, fallback to LIKE
        $scoutAvailable = class_exists(\Laravel\Scout\Searchable::class)
            && in_array(\Laravel\Scout\Searchable::class, class_uses_recursive(Product::class))
            && config('scout.driver')
            && config('scout.driver') !== 'collection';
        if ($scoutAvailable) {
            $tenantId = app()->bound('current_tenant') ? app('current_tenant')?->id : null;
            $products = Product::search($query)
                ->within('products_tenant_'.($tenantId ?? 0))
                ->take(8)
                ->get()
                ->load(['media', 'category'])
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => $product->price,
                        'formatted_price' => $product->formatted_price,
                        'category' => $product->category?->name,
                        'image_url' => $product->primary_image_url ?? asset('images/placeholder.jpg'),
                        'url' => route('products.show', $product),
                    ];
                });

            return response()->json($products);
        }

        // Fallback to LIKE search
        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $term = mb_strtolower($query);
                $q->whereRaw('LOWER(CAST(name AS CHAR)) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(CAST(description AS CHAR)) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$term}%"]);
            })
            ->with(['media', 'category'])
            ->take(8)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'formatted_price' => $product->formatted_price,
                    'category' => $product->category?->name,
                    'image_url' => $product->primary_image_url ?? asset('images/placeholder.jpg'),
                    'url' => route('products.show', $product),
                ];
            });

        return response()->json($products);
    }

    #[OA\Get(
        path: '/search/faceted',
        summary: 'Búsqueda facetada',
        description: 'Búsqueda avanzada con filtros por categoría, marca, precio y disponibilidad. Requiere Meilisearch.',
        tags: ['Catálogo'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', description: 'Término de búsqueda', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category', in: 'query', description: 'Filtrar por nombre de categoría', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'brand', in: 'query', description: 'Filtrar por nombre de marca', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'min_price', in: 'query', description: 'Precio mínimo', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'max_price', in: 'query', description: 'Precio máximo', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'in_stock', in: 'query', description: 'Solo productos en stock', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Resultados paginados con facetas', content: new OA\JsonContent(type: 'object')),
        ]
    )]
    public function searchWithFacets(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $categoryFilter = $request->input('category');
        $brandFilter = $request->input('brand');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $inStock = $request->boolean('in_stock');

        $scoutAvailable = class_exists(\Laravel\Scout\Searchable::class)
            && in_array(\Laravel\Scout\Searchable::class, class_uses_recursive(Product::class))
            && config('scout.driver')
            && config('scout.driver') !== 'collection';

        if ($scoutAvailable) {
            $tenantId = app()->bound('current_tenant') ? app('current_tenant')?->id : null;

            $searchQuery = Product::search($query)
                ->within('products_tenant_'.($tenantId ?? 0));

            if ($categoryFilter) {
                $searchQuery->where('category_name', $categoryFilter);
            }
            if ($brandFilter) {
                $searchQuery->where('brand_name', $brandFilter);
            }
            if ($minPrice !== null) {
                $searchQuery->where('price', '>=', (float) $minPrice);
            }
            if ($maxPrice !== null) {
                $searchQuery->where('price', '<=', (float) $maxPrice);
            }
            if ($inStock) {
                $searchQuery->where('quantity', '>', 0);
            }

            $products = $searchQuery->paginate(12);
        } else {
            // Fallback to Eloquent query
            $eloquentQuery = Product::where('is_active', true)->with(['media', 'category', 'brand']);

            if ($query) {
                $term = mb_strtolower($query);
                $eloquentQuery->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(CAST(name AS CHAR)) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(CAST(description AS CHAR)) LIKE ?', ["%{$term}%"]);
                });
            }
            if ($categoryFilter) {
                $eloquentQuery->whereHas('category', fn ($q) => $q->where('name', $categoryFilter));
            }
            if ($brandFilter) {
                $eloquentQuery->whereHas('brand', fn ($q) => $q->where('name', $brandFilter));
            }
            if ($minPrice !== null) {
                $eloquentQuery->where('price', '>=', (float) $minPrice);
            }
            if ($maxPrice !== null) {
                $eloquentQuery->where('price', '<=', (float) $maxPrice);
            }
            if ($inStock) {
                $eloquentQuery->where('quantity', '>', 0);
            }

            $products = $eloquentQuery->paginate(12);
        }

        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'formatted_price' => $product->formatted_price,
                'compare_price' => $product->compare_price,
                'discount_percentage' => $product->discount_percentage,
                'is_new' => $product->is_new,
                'image_url' => $product->primary_image_url,
                'category_name' => $product->category?->name,
                'brand_name' => $product->brand?->name,
                'url' => route('products.show', $product),
            ];
        });

        return response()->json($products);
    }

    #[OA\Get(
        path: '/search/suggestions',
        summary: 'Sugerencias de búsqueda',
        description: 'Retorna hasta 5 nombres de productos que coinciden con el término, más búsquedas recientes.',
        tags: ['Catálogo'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, description: 'Término parcial (mínimo 2 caracteres)', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Sugerencias y búsquedas recientes', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'suggestions', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'recent', type: 'array', items: new OA\Items(type: 'string')),
                ]
            )),
        ]
    )]
    public function searchSuggestions(Request $request): JsonResponse
    {
        $query = $request->input('q');

        $recent = $request->hasSession() ? $request->session()->get('search_history', []) : [];

        if (! $query || mb_strlen($query) < 2) {
            return response()->json([
                'suggestions' => [],
                'recent' => $recent,
            ]);
        }

        $term = mb_strtolower($query);
        $suggestions = Product::where('is_active', true)
            ->whereRaw('LOWER(CAST(name AS CHAR)) LIKE ?', ["%{$term}%"])
            ->limit(5)
            ->pluck('name');

        return response()->json([
            'suggestions' => $suggestions,
            'recent' => array_slice($recent, 0, 5),
        ]);
    }

    #[OA\Get(
        path: '/categories',
        summary: 'Listar categorías',
        description: 'Retorna todas las categorías activas con su imagen y conteo de productos. Resultado cacheado por 5 minutos.',
        tags: ['Catálogo'],
        responses: [
            new OA\Response(response: 200, description: 'Lista de categorías', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/CategoryItem'))),
        ]
    )]
    public function categories(): JsonResponse
    {
        $cacheService = app(CacheService::class);

        $categories = $cacheService->remember('categories_list', 300, function () {
            return Category::with('media')
                ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                ->whereHas('products', function ($q) {
                    $q->where('is_active', true);
                })
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image_url' => $category->image_url ?? null,
                        'products_count' => $category->products_count,
                    ];
                });
        });

        return response()->json($categories);
    }

    #[OA\Get(
        path: '/products',
        summary: 'Listar productos',
        description: 'Retorna productos activos paginados. Se puede filtrar por categoría, destacados y tendencia.',
        tags: ['Catálogo'],
        parameters: [
            new OA\Parameter(name: 'category_slug', in: 'query', description: 'Filtrar por slug de categoría', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'featured', in: 'query', description: 'Solo productos destacados', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'trending', in: 'query', description: 'Ordenar por más vistos', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Número de página', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Productos paginados', content: new OA\JsonContent(type: 'object')),
        ]
    )]
    public function products(Request $request): JsonResponse
    {
        $cacheService = app(CacheService::class);
        $cacheKey = 'products_' . md5(json_encode($request->all()));

        $products = $cacheService->remember($cacheKey, 120, function () use ($request) {
            $query = Product::where('is_active', true)
                ->with(['media', 'category']);

            if ($request->has('category_slug')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->input('category_slug'));
                });
            }

            if ($request->has('featured')) {
                $query->where('is_featured', true);
            }

            if ($request->has('trending')) {
                $query->orderByDesc('views_count');
            } else {
                $query->latest();
            }

            $products = $query->paginate(12);

            $products->getCollection()->transform(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'formatted_price' => $product->formatted_price,
                    'compare_price' => $product->compare_price,
                    'discount_percentage' => $product->discount_percentage,
                    'is_new' => $product->is_new,
                    'image_url' => $product->primary_image_url,
                    'category_name' => $product->category?->name,
                    'url' => route('products.show', $product),
                ];
            });

            return $products;
        });

        return response()->json($products);
    }
}
