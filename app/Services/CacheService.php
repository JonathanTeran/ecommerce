<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    protected ?array $currentTags = null;

    /**
     * Resolve the current tenant lazily to avoid stale references.
     */
    protected function tenant(): ?object
    {
        return app()->bound('current_tenant') ? app('current_tenant') : null;
    }

    /**
     * Generate a tenant-prefixed cache key.
     */
    public function tenantKey(string $key): string
    {
        $tenantId = $this->tenant()?->id ?? 'global';

        return "tenant_{$tenantId}_{$key}";
    }

    /**
     * Remember a value in cache with tenant-prefixed key.
     */
    public function remember(string $key, int $ttl, \Closure $callback): mixed
    {
        return $this->cacheStore()->remember($this->tenantKey($key), $ttl, $callback);
    }

    /**
     * Forget a specific tenant-prefixed cache key.
     */
    public function forget(string $key): void
    {
        $this->cacheStore()->forget($this->tenantKey($key));
    }

    /**
     * Forget all cache keys matching a given prefix.
     *
     * Always deletes the exact prefixed key. Additionally flushes
     * tagged entries on Redis/Memcached (but not ArrayStore).
     */
    public function forgetByPrefix(string $prefix): void
    {
        $prefixedKey = $this->tenantKey($prefix);
        Cache::forget($prefixedKey);

        $store = Cache::getStore();
        if ($store instanceof \Illuminate\Cache\TaggableStore && ! $store instanceof \Illuminate\Cache\ArrayStore) {
            Cache::tags([$prefixedKey])->flush();
        }
    }

    /**
     * Apply cache tags (only works with Redis/Memcached).
     */
    public function tags(array $tags): static
    {
        $clone = clone $this;
        $clone->currentTags = array_map(fn (string $tag): string => $this->tenantKey($tag), $tags);

        return $clone;
    }

    /**
     * Return the appropriate cache store, with tags if set.
     */
    protected function cacheStore(): \Illuminate\Contracts\Cache\Repository
    {
        if ($this->currentTags && Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
            return Cache::tags($this->currentTags);
        }

        return Cache::store();
    }
}
