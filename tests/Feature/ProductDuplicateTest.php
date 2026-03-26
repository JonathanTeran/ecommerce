<?php

use App\Enums\Module;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
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

it('can duplicate a product', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'iPhone 15 Pro',
        'price' => 999.99,
        'quantity' => 50,
        'is_active' => true,
        'sales_count' => 100,
    ]);

    Livewire::test(ListProducts::class)
        ->callTableAction('duplicate', $product, data: []);

    $duplicate = Product::where('name', 'like', '%iPhone 15 Pro (Copia)%')->first();
    expect($duplicate)->not->toBeNull();
    expect((float) $duplicate->price)->toBe(999.99);
    expect($duplicate->quantity)->toBe(0);
    expect($duplicate->is_active)->toBeFalse();
    expect($duplicate->sales_count)->toBe(0);
    expect($duplicate->views_count)->toBe(0);
});

it('creates a unique slug for duplicated product', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Test Product',
    ]);

    Livewire::test(ListProducts::class)
        ->callTableAction('duplicate', $product, data: []);

    $duplicate = Product::where('name', 'like', '%Test Product (Copia)%')->first();
    expect($duplicate)->not->toBeNull();
    expect($duplicate->slug)->not->toBe($product->slug);
});
