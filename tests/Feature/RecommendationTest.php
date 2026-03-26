<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ProductRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    app()->instance('current_tenant', $this->tenant);
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('tracks product views', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $service = app(ProductRecommendationService::class);
    $service->trackView($product, $this->user->id);

    expect(ProductView::count())->toBe(1);
    expect(ProductView::first()->product_id)->toBe($product->id);
    expect(ProductView::first()->user_id)->toBe($this->user->id);
});

it('prevents duplicate views within 30 minutes', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $service = app(ProductRecommendationService::class);
    $service->trackView($product, $this->user->id);
    $service->trackView($product, $this->user->id);

    expect(ProductView::count())->toBe(1);
});

it('returns frequently bought together products', function () {
    $productA = Product::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
    $productB = Product::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);

    $order = Order::factory()->create(['tenant_id' => $this->tenant->id, 'user_id' => $this->user->id]);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $productA->id,
        'tenant_id' => $this->tenant->id,
        'name' => 'A',
        'sku' => 'SKU-A',
        'price' => 10,
        'quantity' => 1,
        'subtotal' => 10,
    ]);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $productB->id,
        'tenant_id' => $this->tenant->id,
        'name' => 'B',
        'sku' => 'SKU-B',
        'price' => 20,
        'quantity' => 1,
        'subtotal' => 20,
    ]);

    $service = app(ProductRecommendationService::class);
    $recommendations = $service->frequentlyBoughtTogether($productA);

    expect($recommendations)->toHaveCount(1);
    expect($recommendations->first()->id)->toBe($productB->id);
});

it('returns trending products', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);

    ProductView::create([
        'product_id' => $product->id,
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'viewed_at' => now(),
    ]);

    $service = app(ProductRecommendationService::class);
    $trending = $service->trending();

    expect($trending)->not->toBeEmpty();
});

it('exposes recommendation api endpoints', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);

    $this->getJson("/api/products/{$product->slug}/recommendations/bought-together")
        ->assertSuccessful();

    $this->getJson("/api/products/{$product->slug}/recommendations/also-viewed")
        ->assertSuccessful();

    $this->getJson('/api/products/trending')
        ->assertSuccessful();
});
