<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductView;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductRecommendationService
{
    /**
     * Products frequently bought together with the given product.
     * Based on co-occurrence in orders.
     */
    public function frequentlyBoughtTogether(Product $product, int $limit = 4): Collection
    {
        $orderIds = OrderItem::where('product_id', $product->id)
            ->pluck('order_id');

        if ($orderIds->isEmpty()) {
            return collect();
        }

        return Product::whereIn('id', function ($query) use ($orderIds, $product) {
            $query->select('product_id')
                ->from('order_items')
                ->whereIn('order_id', $orderIds)
                ->where('product_id', '!=', $product->id)
                ->groupBy('product_id')
                ->orderByRaw('COUNT(*) DESC');
        })
            ->where('is_active', true)
            ->with('media')
            ->limit($limit)
            ->get();
    }

    /**
     * Products that users who viewed this product also viewed.
     */
    public function customersAlsoViewed(Product $product, int $limit = 4): Collection
    {
        $viewerIds = ProductView::where('product_id', $product->id)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique();

        $viewerSessionIds = ProductView::where('product_id', $product->id)
            ->whereNull('user_id')
            ->whereNotNull('session_id')
            ->pluck('session_id')
            ->unique();

        if ($viewerIds->isEmpty() && $viewerSessionIds->isEmpty()) {
            return collect();
        }

        return Product::whereIn('id', function ($query) use ($viewerIds, $viewerSessionIds, $product) {
            $query->select('product_id')
                ->from('product_views')
                ->where('product_id', '!=', $product->id)
                ->where(function ($q) use ($viewerIds, $viewerSessionIds) {
                    if ($viewerIds->isNotEmpty()) {
                        $q->whereIn('user_id', $viewerIds);
                    }
                    if ($viewerSessionIds->isNotEmpty()) {
                        $q->orWhereIn('session_id', $viewerSessionIds);
                    }
                })
                ->groupBy('product_id')
                ->orderByRaw('COUNT(*) DESC');
        })
            ->where('is_active', true)
            ->with('media')
            ->limit($limit)
            ->get();
    }

    /**
     * Trending products based on most views in the last N days.
     */
    public function trending(int $limit = 8, int $days = 7): Collection
    {
        $productIds = ProductView::where('viewed_at', '>=', now()->subDays($days))
            ->select('product_id', DB::raw('COUNT(*) as view_count'))
            ->groupBy('product_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->pluck('product_id');

        if ($productIds->isEmpty()) {
            return Product::where('is_active', true)
                ->with('media')
                ->orderByDesc('views_count')
                ->limit($limit)
                ->get();
        }

        return Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->with('media')
            ->get()
            ->sortBy(function ($product) use ($productIds) {
                return array_search($product->id, $productIds->toArray());
            })
            ->values();
    }

    /**
     * Track a product view.
     */
    public function trackView(Product $product, ?int $userId = null, ?string $sessionId = null): void
    {
        $recent = ProductView::where('product_id', $product->id)
            ->where('viewed_at', '>=', now()->subMinutes(30))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId && $sessionId, fn ($q) => $q->where('session_id', $sessionId))
            ->exists();

        if (! $recent) {
            ProductView::create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'session_id' => $sessionId,
                'viewed_at' => now(),
            ]);
        }

        $product->incrementViews();
    }
}
