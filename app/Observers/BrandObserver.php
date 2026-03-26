<?php

namespace App\Observers;

use App\Models\Brand;
use App\Services\CacheService;

class BrandObserver
{
    public function __construct(protected CacheService $cacheService) {}

    /**
     * Handle the Brand "created" event.
     */
    public function created(Brand $brand): void
    {
        $this->cacheService->forgetByPrefix('brands');
    }

    /**
     * Handle the Brand "updated" event.
     */
    public function updated(Brand $brand): void
    {
        $this->cacheService->forgetByPrefix('brands');
    }

    /**
     * Handle the Brand "deleted" event.
     */
    public function deleted(Brand $brand): void
    {
        $this->cacheService->forgetByPrefix('brands');
    }
}
