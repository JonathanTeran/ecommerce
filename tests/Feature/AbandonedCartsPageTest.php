<?php

use App\Enums\Module;
use App\Filament\Pages\AbandonedCarts;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createCart(array $attributes = []): Cart
{
    return Cart::create(array_merge([
        'session_id' => fake()->uuid(),
        'expires_at' => now()->subDay(),
        'reminder_count' => 0,
    ], $attributes));
}

function createCartItem(array $attributes = []): CartItem
{
    return CartItem::create(array_merge([
        'quantity' => 1,
        'price' => 25.00,
    ], $attributes));
}

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

it('can render the abandoned carts page', function () {
    Livewire::test(AbandonedCarts::class)
        ->assertSuccessful();
});

it('requires cart module for access', function () {
    expect(AbandonedCarts::canAccess())->toBeTrue();

    app()->forgetInstance('current_tenant');

    expect(AbandonedCarts::canAccess())->toBeFalse();
});

it('shows abandoned carts with items', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $cart = createCart([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->admin->id,
    ]);

    createCartItem([
        'tenant_id' => $this->tenant->id,
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'price' => 25.00,
        'quantity' => 2,
    ]);

    Livewire::test(AbandonedCarts::class)
        ->assertCanSeeTableRecords([$cart]);
});

it('does not show active carts as abandoned', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $activeCart = createCart([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->admin->id,
        'expires_at' => now()->addDay(),
    ]);
    // Force updated_at to be recent
    $activeCart->touch();

    createCartItem([
        'tenant_id' => $this->tenant->id,
        'cart_id' => $activeCart->id,
        'product_id' => $product->id,
    ]);

    Livewire::test(AbandonedCarts::class)
        ->assertCanNotSeeTableRecords([$activeCart]);
});

it('can send reminder for cart with user', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);
    $customer = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $cart = createCart([
        'tenant_id' => $this->tenant->id,
        'user_id' => $customer->id,
    ]);

    createCartItem([
        'tenant_id' => $this->tenant->id,
        'cart_id' => $cart->id,
        'product_id' => $product->id,
    ]);

    Livewire::test(AbandonedCarts::class)
        ->callTableAction('send_reminder', $cart);

    expect($cart->fresh()->reminder_count)->toBe(1);
    expect($cart->fresh()->reminder_sent_at)->not->toBeNull();
});

it('computes stats correctly', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $cart = createCart([
        'tenant_id' => $this->tenant->id,
    ]);

    createCartItem([
        'tenant_id' => $this->tenant->id,
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'price' => 50.00,
        'quantity' => 2,
    ]);

    $page = Livewire::test(AbandonedCarts::class);

    $stats = $page->instance()->getStats();
    expect($stats['total_abandoned'])->toBeGreaterThanOrEqual(1);
    expect($stats['total_value'])->toBeGreaterThanOrEqual(100);
});
