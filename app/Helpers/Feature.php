<?php

namespace App\Helpers;

use App\Models\FeatureFlag;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class Feature
{
    /**
     * Check if a given feature key is enabled for the currently resolved tenant.
     * The evaluation order is:
     * 1. Does the feature exist? If not, return false (or default).
     * 2. If it is enabled globally: check if current tenant is in disabled_tenants. If so, return false. Otherwise true.
     * 3. If it is NOT enabled globally: check if current tenant is in enabled_tenants. If so, return true. Otherwise false.
     *
     * @param string $key
     * @param Tenant|null $tenant
     * @return bool
     */
    public static function isEnabled(string $key, ?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? app('current_tenant') ?? null;
        
        // We use cache to avoid querying the DB for flags on every request
        // The cache will store the feature object properties
        $feature = Cache::remember("feature_flag_{$key}", 3600, function () use ($key) {
            return FeatureFlag::where('key', $key)->first();
        });

        if (!$feature) {
            return false;
        }

        // If no tenant context is provided, we can only evaluate global status, 
        // assuming it's not restricted to certain tenants over global
        if (!$tenant) {
            return $feature->is_enabled_globally;
        }

        $tenantId = (string) $tenant->id;

        if ($feature->is_enabled_globally) {
            // Check if tenant is explicitly disabled
            $disabled = $feature->disabled_tenants ?? [];
            if (in_array($tenantId, $disabled)) {
                return false;
            }
            return true;
        } else {
            // Check if tenant is explicitly enabled
            $enabled = $feature->enabled_tenants ?? [];
            if (in_array($tenantId, $enabled)) {
                return true;
            }
            return false;
        }
    }
}
