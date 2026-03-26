<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CacheService;

class ProductObserver
{
    public function __construct(protected CacheService $cacheService) {}

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->invalidateCache($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->invalidateCache($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->invalidateCache($product);
    }

    /**
     * Flush product and category caches for the product's tenant.
     */
    protected function invalidateCache(Product $product): void
    {
        $this->cacheService->forgetByPrefix('products');
        $this->cacheService->forgetByPrefix('categories');
        $this->cacheService->forget('categories_list');
    }
}
