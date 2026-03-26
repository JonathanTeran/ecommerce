<?php

use App\Enums\Module;
use App\Livewire\WishlistButton;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

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
        'name' => 'Test Product', 'slug' => 'test-product', 'price' => 99.99,
        'quantity' => 5, 'sku' => 'TST-001', 'is_active' => true,
        'tenant_id' => $this->tenant->id, 'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('authenticated user can add product to wishlist', function () {
    $this->actingAs($this->user);

    Livewire::test(WishlistButton::class, ['product' => $this->product])
        ->call('toggleWishlist');

    expect(Wishlist::where('user_id', $this->user->id)->where('product_id', $this->product->id)->exists())->toBeTrue();
});

it('authenticated user can remove product from wishlist', function () {
    $this->actingAs($this->user);

    Wishlist::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
    ]);

    Livewire::test(WishlistButton::class, ['product' => $this->product])
        ->call('toggleWishlist');

    expect(Wishlist::where('user_id', $this->user->id)->where('product_id', $this->product->id)->exists())->toBeFalse();
});

it('wishlist page loads for authenticated user', function () {
    $this->actingAs($this->user);

    Wishlist::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
    ]);

    $this->get('/account/wishlist')->assertSuccessful();
});

it('wishlist is scoped per user', function () {
    $otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Wishlist::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);
    Wishlist::create(['user_id' => $otherUser->id, 'product_id' => $this->product->id]);

    expect(Wishlist::where('user_id', $this->user->id)->count())->toBe(1)
        ->and(Wishlist::where('user_id', $otherUser->id)->count())->toBe(1);
});
