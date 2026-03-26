<?php

use App\Enums\Module;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['is_default' => true]);
    $this->plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $this->plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);

    // Set tenant so factory/seeding works
    app()->instance('current_tenant', $this->tenant);

    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
        'tax_rate' => 15.00,
    ]);

    $this->category = Category::create([
        'name' => 'Electronics', 'slug' => 'electronics',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => true, 'position' => 0,
    ]);

    $this->brand = Brand::create([
        'name' => 'Apple', 'slug' => 'apple',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);
});

it('resolves product page via middleware tenant without pre-set tenant', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'is_active' => true,
    ]);

    // Clear the pre-set tenant so the middleware must resolve it
    app()->forgetInstance('current_tenant');

    $this->get('/products/' . $product->slug)
        ->assertSuccessful();
});

it('returns 404 for product belonging to different tenant', function () {
    $otherTenant = Tenant::factory()->create(['is_default' => false]);

    $product = Product::factory()->create([
        'tenant_id' => $otherTenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'is_active' => true,
    ]);

    // Clear tenant so middleware resolves default tenant
    app()->forgetInstance('current_tenant');

    $this->get('/products/' . $product->slug)
        ->assertNotFound();
});
