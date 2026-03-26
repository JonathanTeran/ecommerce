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

it('searches products by SKU', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'sku' => 'LAPTOP-GAMER-001',
        'is_active' => true,
    ]);

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'sku' => 'MOUSE-WIRELESS-002',
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/search?q=LAPTOP');

    $response->assertOk();

    $results = $response->json();
    expect($results)->toHaveCount(1);
});

it('returns empty results for empty query', function () {
    $response = $this->getJson('/api/search?q=');

    $response->assertOk();
    expect($response->json())->toBeEmpty();
});

it('returns search suggestions structure', function () {
    $response = $this->getJson('/api/search/suggestions?q=ab');

    $response->assertOk()
        ->assertJsonStructure(['suggestions', 'recent']);
});

it('returns empty suggestions for short query', function () {
    $response = $this->getJson('/api/search/suggestions?q=a');

    $response->assertOk()
        ->assertJson(['suggestions' => [], 'recent' => []]);
});
