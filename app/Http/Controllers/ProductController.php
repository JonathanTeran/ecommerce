<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductRecommendationService;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(
        private ProductRecommendationService $recommendationService,
    ) {}

    public function show(Product $product)
    {
        if (! $product->is_active) {
            abort(404);
        }

        $product->load(['category', 'brand', 'variants', 'images']);

        // Track product view
        $this->recommendationService->trackView(
            $product,
            auth()->id(),
            session()->getId()
        );

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['media', 'brand', 'category', 'reviews'])
            ->take(4)
            ->get();

        // Recommendations
        $boughtTogether = $this->recommendationService->frequentlyBoughtTogether($product);
        $alsoViewed = $this->recommendationService->customersAlsoViewed($product);

        $seo = [
            'title' => $product->meta_title ?: $product->name,
            'metaDescription' => $product->meta_description ?: Str::limit(strip_tags($product->description), 160),
            'ogImage' => $product->primary_image_url,
            'ogType' => 'product',
            'canonical' => route('products.show', $product),
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->name,
                'description' => strip_tags($product->short_description ?? $product->description ?? ''),
                'image' => $product->primary_image_url,
                'sku' => $product->sku,
                'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
                'category' => $product->category?->name,
                'offers' => [
                    '@type' => 'Offer',
                    'price' => $product->price,
                    'priceCurrency' => 'USD',
                    'availability' => $product->is_in_stock
                        ? 'https://schema.org/InStock'
                        : 'https://schema.org/OutOfStock',
                    'url' => route('products.show', $product),
                ],
            ],
        ];

        if ($product->average_rating > 0) {
            $seo['jsonLd']['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($product->average_rating, 1),
                'reviewCount' => $product->reviews_count,
            ];
        }

        return view('products.show', compact('product', 'relatedProducts', 'boughtTogether', 'alsoViewed', 'seo'));
    }
}
