<?php

namespace App\Observers;

use App\Models\FeatureFlag;

class FeatureFlagObserver
{
    /**
     * Clear the cache for the given feature flag.
     */
    protected function clearCache(FeatureFlag $featureFlag): void
    {
        \Illuminate\Support\Facades\Cache::forget("feature_flag_{$featureFlag->key}");
    }

    /**
     * Handle the FeatureFlag "created" event.
     */
    public function created(FeatureFlag $featureFlag): void
    {
        $this->clearCache($featureFlag);
    }

    /**
     * Handle the FeatureFlag "updated" event.
     */
    public function updated(FeatureFlag $featureFlag): void
    {
        $this->clearCache($featureFlag);
    }

    /**
     * Handle the FeatureFlag "deleted" event.
     */
    public function deleted(FeatureFlag $featureFlag): void
    {
        $this->clearCache($featureFlag);
    }

    /**
     * Handle the FeatureFlag "restored" event.
     */
    public function restored(FeatureFlag $featureFlag): void
    {
        $this->clearCache($featureFlag);
    }

    /**
     * Handle the FeatureFlag "force deleted" event.
     */
    public function forceDeleted(FeatureFlag $featureFlag): void
    {
        $this->clearCache($featureFlag);
    }
}
