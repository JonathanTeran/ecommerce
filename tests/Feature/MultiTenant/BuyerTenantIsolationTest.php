<?php

use App\Enums\Module;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Tenant A - the "current" tenant
    $this->tenantA = Tenant::factory()->create(['slug' => 'store-a']);
    $this->plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenantA->id, 'plan_id' => $this->plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    GeneralSetting::create([
        'tenant_id' => $this->tenantA->id,
        'site_name' => 'Store A',
        'tax_rate' => 15.00,
    ]);

    // Tenant B - another tenant
    $this->tenantB = Tenant::factory()->create(['slug' => 'store-b']);
    Subscription::create([
        'tenant_id' => $this->tenantB->id, 'plan_id' => $this->plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    GeneralSetting::create([
        'tenant_id' => $this->tenantB->id,
        'site_name' => 'Store B',
        'tax_rate' => 12.00,
    ]);

    // Set current tenant to A
    app()->instance('current_tenant', $this->tenantA);

    // Categories and products per tenant
    $this->categoryA = Category::create([
        'name' => 'Electronics A', 'slug' => 'electronics-a', 'tenant_id' => $this->tenantA->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $this->productA = Product::factory()->create([
        'tenant_id' => $this->tenantA->id,
        'category_id' => $this->categoryA->id,
        'is_active' => true,
        'quantity' => 50,
    ]);

    // Create tenant B products without global scope
    $this->categoryB = Category::withoutGlobalScopes()->create([
        'name' => 'Electronics B', 'slug' => 'electronics-b', 'tenant_id' => $this->tenantB->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $this->productB = Product::withoutGlobalScopes()->create([
        'name' => 'Product B', 'slug' => 'product-b-' . uniqid(), 'sku' => 'SKU-B-' . uniqid(),
        'price' => 99.99, 'quantity' => 20,
        'tenant_id' => $this->tenantB->id,
        'category_id' => $this->categoryB->id,
        'is_active' => true,
    ]);

    // Buyer for tenant A
    $this->buyerA = User::factory()->create(['tenant_id' => $this->tenantA->id]);

    // Buyer for tenant B
    $this->buyerB = User::factory()->create(['tenant_id' => $this->tenantB->id]);
});

// ========================================
// PRODUCT VISIBILITY ISOLATION
// ========================================

it('buyer only sees products from their tenant', function () {
    $products = Product::where('is_active', true)->get();

    expect($products->contains($this->productA))->toBeTrue()
        ->and($products->contains($this->productB))->toBeFalse();
});

it('buyer cannot access product from another tenant via URL', function () {
    $this->actingAs($this->buyerA);

    $response = $this->get('/products/' . $this->productB->slug);
    $response->assertNotFound();
});

// ========================================
// CART ISOLATION
// ========================================

it('buyer cart is scoped to their tenant', function () {
    $this->actingAs($this->buyerA);

    $cartA = Cart::create([
        'user_id' => $this->buyerA->id,
        'tenant_id' => $this->tenantA->id,
    ]);

    // Create cart for tenant B without scope
    Cart::withoutGlobalScopes()->create([
        'user_id' => $this->buyerB->id,
        'tenant_id' => $this->tenantB->id,
    ]);

    $visibleCarts = Cart::all();
    expect($visibleCarts)->toHaveCount(1)
        ->and($visibleCarts->first()->id)->toBe($cartA->id);
});

// ========================================
// ORDER ISOLATION
// ========================================

it('buyer only sees their own orders from their tenant', function () {
    $this->actingAs($this->buyerA);

    $orderA = Order::withoutGlobalScopes()->create([
        'user_id' => $this->buyerA->id,
        'tenant_id' => $this->tenantA->id,
        'order_number' => 'ORD-A-001',
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => 100,
        'total' => 115,
        'tax_amount' => 15,
    ]);

    Order::withoutGlobalScopes()->create([
        'user_id' => $this->buyerB->id,
        'tenant_id' => $this->tenantB->id,
        'order_number' => 'ORD-B-001',
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => 50,
        'total' => 56,
        'tax_amount' => 6,
    ]);

    $visibleOrders = Order::all();
    expect($visibleOrders)->toHaveCount(1)
        ->and($visibleOrders->first()->order_number)->toBe('ORD-A-001');
});

it('buyer cannot access order from another tenant via URL', function () {
    $this->actingAs($this->buyerA);

    $orderB = Order::withoutGlobalScopes()->create([
        'user_id' => $this->buyerB->id,
        'tenant_id' => $this->tenantB->id,
        'order_number' => 'ORD-B-002',
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => 50,
        'total' => 56,
        'tax_amount' => 6,
    ]);

    $response = $this->get('/account/orders/' . $orderB->id);
    // Returns 404 because global tenant scope filters out the order entirely
    $response->assertNotFound();
});

// ========================================
// WISHLIST ISOLATION
// ========================================

it('buyer wishlists are scoped to their tenant', function () {
    Wishlist::create([
        'user_id' => $this->buyerA->id,
        'product_id' => $this->productA->id,
        'tenant_id' => $this->tenantA->id,
    ]);

    Wishlist::withoutGlobalScopes()->create([
        'user_id' => $this->buyerB->id,
        'product_id' => $this->productB->id,
        'tenant_id' => $this->tenantB->id,
    ]);

    $visibleWishlists = Wishlist::all();
    expect($visibleWishlists)->toHaveCount(1)
        ->and($visibleWishlists->first()->user_id)->toBe($this->buyerA->id);
});

// ========================================
// COUPON ISOLATION
// ========================================

it('buyer cannot use coupons from another tenant', function () {
    Coupon::factory()->create([
        'code' => 'TENANT-A-ONLY',
        'tenant_id' => $this->tenantA->id,
    ]);

    Coupon::withoutGlobalScopes()->create([
        'code' => 'TENANT-B-ONLY',
        'name' => 'B Only',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
        'tenant_id' => $this->tenantB->id,
        'usage_count' => 0,
        'first_order_only' => false,
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addMonth(),
    ]);

    $couponA = Coupon::where('code', 'TENANT-A-ONLY')->first();
    $couponB = Coupon::where('code', 'TENANT-B-ONLY')->first();

    expect($couponA)->not->toBeNull()
        ->and($couponB)->toBeNull();
});

// ========================================
// REVIEW ISOLATION
// ========================================

it('reviews are scoped to tenant', function () {
    Review::withoutGlobalScopes()->create([
        'user_id' => $this->buyerA->id,
        'product_id' => $this->productA->id,
        'tenant_id' => $this->tenantA->id,
        'rating' => 5,
        'title' => 'Great A',
        'comment' => 'Love it',
        'is_approved' => true,
    ]);

    Review::withoutGlobalScopes()->create([
        'user_id' => $this->buyerB->id,
        'product_id' => $this->productB->id,
        'tenant_id' => $this->tenantB->id,
        'rating' => 3,
        'title' => 'OK B',
        'comment' => 'Decent',
        'is_approved' => true,
    ]);

    $visibleReviews = Review::all();
    expect($visibleReviews)->toHaveCount(1)
        ->and($visibleReviews->first()->title)->toBe('Great A');
});

// ========================================
// CATEGORY ISOLATION
// ========================================

it('categories are scoped to tenant', function () {
    $categories = Category::all();
    expect($categories->pluck('name')->toArray())->toContain('Electronics A')
        ->and($categories->pluck('name')->toArray())->not->toContain('Electronics B');
});

// ========================================
// STOREFRONT PAGES PER TENANT
// ========================================

it('homepage shows tenant-specific branding', function () {
    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('Store A');
});

it('shop page shows only tenant products', function () {
    $response = $this->get('/shop');
    $response->assertSuccessful();
    $response->assertSee($this->productA->name);
    $response->assertDontSee('Product B');
});

// ========================================
// API ISOLATION
// ========================================

it('search API returns only tenant products', function () {
    $response = $this->getJson('/api/search?q=' . substr($this->productA->name, 0, 3));
    $response->assertSuccessful();

    $ids = collect($response->json())->pluck('id');
    expect($ids->contains($this->productB->id))->toBeFalse();
});

// ========================================
// STORE POLICIES PER TENANT
// ========================================

it('store policies are tenant-specific', function () {
    $settingsA = GeneralSetting::first();
    $settingsA->update([
        'store_policies' => [
            ['title' => 'Devoluciones A', 'slug' => 'devoluciones', 'is_active' => true, 'content' => '<p>Policy A</p>'],
        ],
    ]);

    $response = $this->get('/politicas/devoluciones');
    $response->assertSuccessful();
    $response->assertSee('Devoluciones A');
    $response->assertSee('Policy A');
});
