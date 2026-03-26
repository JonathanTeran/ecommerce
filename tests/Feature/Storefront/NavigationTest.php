<?php

use App\Enums\Module;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'test-store', 'is_default' => true]);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->forgetInstance('current_tenant');
});

it('home page loads', function () {
    $this->get('/')->assertSuccessful();
});

it('shop page loads', function () {
    $this->get('/shop')->assertSuccessful();
});

it('categories page loads', function () {
    Category::create([
        'name' => 'Test Category', 'slug' => 'test-category',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);
    $this->get('/categories')->assertSuccessful();
});

it('brands page loads', function () {
    Brand::create([
        'name' => 'Test Brand', 'slug' => 'test-brand',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);
    $this->get('/brands')->assertSuccessful();
});

it('product detail page loads', function () {
    app()->instance('current_tenant', $this->tenant);
    $category = Category::create([
        'name' => 'Cat', 'slug' => 'cat', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $brand = Brand::create([
        'name' => 'Brand', 'slug' => 'brand', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $product = Product::create([
        'name' => 'Test Product', 'slug' => 'test-product', 'price' => 99.99,
        'quantity' => 5, 'sku' => 'TST-001', 'is_active' => true,
        'tenant_id' => $this->tenant->id, 'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $this->get("/products/{$product->slug}")->assertSuccessful();
});

it('about page loads', function () {
    $this->get('/about')->assertSuccessful();
});
