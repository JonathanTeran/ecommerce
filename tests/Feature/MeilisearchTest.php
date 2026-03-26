<?php

use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'test-store']);
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

it('product is searchable', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Laptop Gaming Pro',
        'is_active' => true,
    ]);

    expect($product->shouldBeSearchable())->toBeTrue();
    expect($product->searchableAs())->toBe('products_tenant_'.$this->tenant->id);
});

it('inactive product should not be searchable', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => false,
    ]);

    expect($product->shouldBeSearchable())->toBeFalse();
});

it('searchable array contains required fields', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'price' => 99.99,
    ]);

    $searchable = $product->toSearchableArray();

    expect($searchable)->toHaveKeys(['id', 'name', 'sku', 'price', 'is_active', 'is_featured', 'category_name', 'brand_name']);
    expect($searchable['price'])->toBe(99.99);
});

it('uses tenant-prefixed search index', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $otherTenant = Tenant::factory()->create();
    $otherProduct = Product::factory()->create(['tenant_id' => $otherTenant->id]);

    expect($product->searchableAs())->not->toBe($otherProduct->searchableAs());
});

it('search api returns results', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'name' => 'Laptop Gaming XYZ',
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/search?q=Laptop');
    $response->assertSuccessful();
});

it('faceted search endpoint exists', function () {
    $response = $this->getJson('/api/search/faceted?q=test');
    $response->assertSuccessful();
});
