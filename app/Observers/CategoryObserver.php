<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CacheService;

class CategoryObserver
{
    public function __construct(protected CacheService $cacheService) {}

    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        $this->cacheService->forgetByPrefix('categories');
        $this->cacheService->forget('categories_list');
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        $this->cacheService->forgetByPrefix('categories');
        $this->cacheService->forget('categories_list');
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->cacheService->forgetByPrefix('categories');
        $this->cacheService->forget('categories_list');
    }
}
