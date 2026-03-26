<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductRecommendationService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Recomendaciones', description: 'Motor de recomendaciones de productos')]
class RecommendationApiController extends Controller
{
    public function __construct(
        private ProductRecommendationService $recommendationService,
    ) {}

    #[OA\Get(
        path: '/products/{product}/recommendations/bought-together',
        summary: 'Frecuentemente comprados juntos',
        description: 'Retorna productos que los clientes frecuentemente compran junto con el producto indicado, basado en historial de órdenes.',
        tags: ['Recomendaciones'],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, description: 'Slug del producto', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Productos recomendados', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductRecommendation'))),
        ]
    )]
    public function boughtTogether(Product $product): JsonResponse
    {
        $products = $this->recommendationService->frequentlyBoughtTogether($product);

        return response()->json($this->transformProducts($products));
    }

    #[OA\Get(
        path: '/products/{product}/recommendations/also-viewed',
        summary: 'Clientes también vieron',
        description: 'Retorna productos que otros clientes también vieron después de ver este producto.',
        tags: ['Recomendaciones'],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, description: 'Slug del producto', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Productos recomendados', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductRecommendation'))),
        ]
    )]
    public function alsoViewed(Product $product): JsonResponse
    {
        $products = $this->recommendationService->customersAlsoViewed($product);

        return response()->json($this->transformProducts($products));
    }

    #[OA\Get(
        path: '/products/trending',
        summary: 'Productos en tendencia',
        description: 'Retorna los productos más vistos en los últimos 7 días.',
        tags: ['Recomendaciones'],
        responses: [
            new OA\Response(response: 200, description: 'Productos trending', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductRecommendation'))),
        ]
    )]
    public function trending(): JsonResponse
    {
        $products = $this->recommendationService->trending();

        return response()->json($this->transformProducts($products));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     * @return array<int, array<string, mixed>>
     */
    private function transformProducts($products): array
    {
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'formatted_price' => $product->formatted_price,
                'image_url' => $product->primary_image_url,
                'url' => route('products.show', $product),
            ];
        })->toArray();
    }
}
