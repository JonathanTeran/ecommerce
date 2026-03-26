<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => ['products', 'categories', 'storefront'],
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);
});

it('generates tenant-prefixed cache keys', function () {
    $service = app(CacheService::class);

    expect($service->tenantKey('test'))->toBe("tenant_{$this->tenant->id}_test");
});

it('generates correct key for categories prefix', function () {
    $service = app(CacheService::class);

    expect($service->tenantKey('categories_list'))
        ->toBe("tenant_{$this->tenant->id}_categories_list");
});

it('generates correct key for products prefix with hash', function () {
    $service = app(CacheService::class);
    $params = ['category_slug' => 'electronics', 'featured' => '1'];
    $cacheKey = 'products_' . md5(json_encode($params));

    expect($service->tenantKey($cacheKey))
        ->toStartWith("tenant_{$this->tenant->id}_products_");
});

it('caches categories on first API call', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);
    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $expectedKey = "tenant_{$this->tenant->id}_categories_list";

    expect(Cache::has($expectedKey))->toBeFalse();

    $this->getJson('/api/categories')->assertOk();

    expect(Cache::has($expectedKey))->toBeTrue();
});

it('caches products on first API call', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);
    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $cacheKey = "tenant_{$this->tenant->id}_products_" . md5(json_encode([]));

    expect(Cache::has($cacheKey))->toBeFalse();

    $this->getJson('/api/products')->assertOk();

    expect(Cache::has($cacheKey))->toBeTrue();
});

it('invalidates categories cache when product is created', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);
    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $this->getJson('/api/categories')->assertOk();

    $cacheKey = "tenant_{$this->tenant->id}_categories_list";
    expect(Cache::has($cacheKey))->toBeTrue();

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('invalidates cache when product is updated', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $this->getJson('/api/categories')->assertOk();

    $cacheKey = "tenant_{$this->tenant->id}_categories_list";
    expect(Cache::has($cacheKey))->toBeTrue();

    $product->update(['price' => 999]);

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('invalidates cache when product is deleted', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $this->getJson('/api/categories')->assertOk();

    $cacheKey = "tenant_{$this->tenant->id}_categories_list";
    expect(Cache::has($cacheKey))->toBeTrue();

    $product->delete();

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('cache service forget clears correct key', function () {
    $service = app(CacheService::class);
    $brandsKey = $service->tenantKey('brands');

    Cache::put($brandsKey, ['cached-brands'], 300);
    expect(Cache::has($brandsKey))->toBeTrue();

    $service->forget('brands');

    expect(Cache::has($brandsKey))->toBeFalse();
});

it('cache service forgetByPrefix clears correct key', function () {
    $service = app(CacheService::class);
    $brandsKey = $service->tenantKey('brands');

    Cache::put($brandsKey, ['cached-brands'], 300);
    expect(Cache::has($brandsKey))->toBeTrue();

    $service->forgetByPrefix('brands');

    expect(Cache::has($brandsKey))->toBeFalse();
});

it('brand observer clears brands cache', function () {
    $service = app(CacheService::class);
    $brandsKey = $service->tenantKey('brands');

    Cache::put($brandsKey, ['cached-brands'], 300);
    expect(Cache::has($brandsKey))->toBeTrue();

    $observer = app(\App\Observers\BrandObserver::class);
    $brand = Brand::factory()->make(['tenant_id' => $this->tenant->id]);
    $observer->created($brand);

    expect(Cache::has($brandsKey))->toBeFalse();
});

it('category observer clears categories cache', function () {
    $service = app(CacheService::class);
    $categoriesKey = $service->tenantKey('categories');
    $categoriesListKey = $service->tenantKey('categories_list');

    Cache::put($categoriesKey, ['cached-categories'], 300);
    Cache::put($categoriesListKey, ['cached-list'], 300);
    expect(Cache::has($categoriesKey))->toBeTrue();

    $observer = app(\App\Observers\CategoryObserver::class);
    $category = Category::factory()->make(['tenant_id' => $this->tenant->id]);
    $observer->created($category);

    expect(Cache::has($categoriesKey))->toBeFalse();
    expect(Cache::has($categoriesListKey))->toBeFalse();
});

it('ensures tenant isolation for cache keys', function () {
    $tenant2 = Tenant::factory()->create();

    $service1 = app(CacheService::class);
    $key1 = $service1->tenantKey('products_list');

    app()->instance('current_tenant', $tenant2);
    $service2 = new CacheService;
    $key2 = $service2->tenantKey('products_list');

    expect($key1)->not->toBe($key2)
        ->and($key1)->toBe("tenant_{$this->tenant->id}_products_list")
        ->and($key2)->toBe("tenant_{$tenant2->id}_products_list");
});

it('does not share cached data between tenants', function () {
    $tenant2 = Tenant::factory()->create();

    $service1 = app(CacheService::class);
    $service1->remember('shared_key', 300, fn () => 'tenant_1_data');

    app()->instance('current_tenant', $tenant2);
    $service2 = new CacheService;
    $result = $service2->remember('shared_key', 300, fn () => 'tenant_2_data');

    expect($result)->toBe('tenant_2_data');
});
