<?php

use App\Enums\Module;
use App\Filament\Pages\WishlistAnalytics;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wishlist;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

    $this->tenant = Tenant::factory()->create();
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

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render the wishlist analytics page', function () {
    Livewire::test(WishlistAnalytics::class)
        ->assertSuccessful();
});

it('requires products module for access', function () {
    expect(WishlistAnalytics::canAccess())->toBeTrue();

    app()->forgetInstance('current_tenant');

    expect(WishlistAnalytics::canAccess())->toBeFalse();
});

it('shows wishlisted products in table', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Producto Deseado',
    ]);

    Wishlist::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->admin->id,
        'product_id' => $product->id,
    ]);

    Livewire::test(WishlistAnalytics::class)
        ->assertCanSeeTableRecords([$product]);
});

it('does not show products without wishlists', function () {
    $wishlisted = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Con Wishlist',
    ]);
    $notWishlisted = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Sin Wishlist',
    ]);

    Wishlist::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->admin->id,
        'product_id' => $wishlisted->id,
    ]);

    Livewire::test(WishlistAnalytics::class)
        ->assertCanSeeTableRecords([$wishlisted])
        ->assertCanNotSeeTableRecords([$notWishlisted]);
});

it('computes stats correctly', function () {
    $product1 = Product::factory()->create(['tenant_id' => $this->tenant->id, 'quantity' => 10]);
    $product2 = Product::factory()->create(['tenant_id' => $this->tenant->id, 'quantity' => 0]);

    $customer = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Wishlist::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->admin->id, 'product_id' => $product1->id]);
    Wishlist::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->admin->id, 'product_id' => $product2->id]);
    Wishlist::create(['tenant_id' => $this->tenant->id, 'user_id' => $customer->id, 'product_id' => $product1->id]);

    $page = Livewire::test(WishlistAnalytics::class);

    $stats = $page->instance()->getStats();
    expect($stats['total_wishlisted'])->toBe(3);
    expect($stats['unique_products'])->toBe(2);
    expect($stats['unique_users'])->toBe(2);
    expect($stats['out_of_stock_wishlisted'])->toBe(1);
});

it('sorts by wishlist count by default', function () {
    $product1 = Product::factory()->create(['tenant_id' => $this->tenant->id]);
    $product2 = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $customer1 = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $customer2 = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Wishlist::create(['tenant_id' => $this->tenant->id, 'user_id' => $customer1->id, 'product_id' => $product1->id]);
    Wishlist::create(['tenant_id' => $this->tenant->id, 'user_id' => $customer2->id, 'product_id' => $product1->id]);
    Wishlist::create(['tenant_id' => $this->tenant->id, 'user_id' => $customer1->id, 'product_id' => $product2->id]);

    Livewire::test(WishlistAnalytics::class)
        ->assertCanSeeTableRecords([$product1, $product2]);
});
