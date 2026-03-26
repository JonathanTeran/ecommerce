<?php

use App\Enums\Module;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'test-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);

    $category = Category::create([
        'name' => 'Cat', 'slug' => 'cat', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $brand = Brand::create([
        'name' => 'Brand', 'slug' => 'brand', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $this->product = Product::create([
        'name' => 'Test Product', 'slug' => 'test-product', 'price' => 100.00,
        'quantity' => 10, 'sku' => 'TST-001', 'is_active' => true,
        'tenant_id' => $this->tenant->id, 'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);
    $this->secondProduct = Product::create([
        'name' => 'Second Product', 'slug' => 'second-product', 'price' => 50.00,
        'quantity' => 5, 'sku' => 'TST-002', 'is_active' => true,
        'tenant_id' => $this->tenant->id, 'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('can add item to cart', function () {
    $cart = Cart::create(['user_id' => $this->user->id]);
    $cart->addItem($this->product, 2);

    expect($cart->items()->count())->toBe(1)
        ->and($cart->items->first()->quantity)->toBe(2)
        ->and($cart->items->first()->price)->toBe('100.00');
});

it('can update item quantity', function () {
    $cart = Cart::create(['user_id' => $this->user->id]);
    $cart->addItem($this->product, 1);
    $cart->updateItem($this->product->id, 5);

    expect($cart->items->first()->fresh()->quantity)->toBe(5);
});

it('can remove item from cart', function () {
    $cart = Cart::create(['user_id' => $this->user->id]);
    $cart->addItem($this->product, 1);
    $cart->addItem($this->secondProduct, 1);

    $cart->removeItem($this->product->id);

    expect($cart->items()->count())->toBe(1);
});

it('can clear cart', function () {
    $cart = Cart::create(['user_id' => $this->user->id]);
    $cart->addItem($this->product, 2);
    $cart->addItem($this->secondProduct, 1);

    $cart->clear();

    expect($cart->items()->count())->toBe(0);
});

it('calculates subtotal correctly', function () {
    $cart = Cart::create(['user_id' => $this->user->id]);
    $cart->addItem($this->product, 2);       // 2 x 100 = 200
    $cart->addItem($this->secondProduct, 3); // 3 x 50 = 150

    $cart->load('items');
    expect((float) $cart->subtotal)->toBe(350.00);
});

it('calculates items count correctly', function () {
    $cart = Cart::create(['user_id' => $this->user->id]);
    $cart->addItem($this->product, 2);
    $cart->addItem($this->secondProduct, 3);

    $cart->load('items');
    expect($cart->items_count)->toBe(5);
});
