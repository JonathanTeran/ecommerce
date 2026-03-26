<?php

use App\Enums\Module;
use App\Filament\Resources\InventoryMovementResource;
use App\Filament\Resources\InventoryMovementResource\Pages\ListInventoryMovements;
use App\Filament\Resources\InventoryMovementResource\Pages\ViewInventoryMovement;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WarehouseLocation;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

    $this->tenant = Tenant::factory()->create();
    $this->plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $this->plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    $this->category = Category::create([
        'name' => 'Parts', 'slug' => 'parts', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);

    $this->product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
    ]);
});

it('can render inventory movements list page (kardex)', function () {
    $this->get(InventoryMovementResource::getUrl('index'))->assertSuccessful();
});

it('can list inventory movements in table', function () {
    $movement = InventoryMovement::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'purchase',
        'quantity' => 50,
        'unit_cost' => 10.00,
        'total_cost' => 500.00,
        'balance_quantity' => 50,
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(ListInventoryMovements::class)
        ->assertCanSeeTableRecords([$movement]);
});

it('can view inventory movement details', function () {
    $movement = InventoryMovement::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'purchase',
        'quantity' => 100,
        'unit_cost' => 5.00,
        'total_cost' => 500.00,
        'balance_quantity' => 100,
        'user_id' => $this->admin->id,
        'notes' => 'Initial stock purchase',
    ]);

    $this->get(InventoryMovementResource::getUrl('view', ['record' => $movement]))
        ->assertSuccessful();
});

it('enforces tenant scoping on inventory movements', function () {
    $otherTenant = Tenant::factory()->create();
    $otherCategory = Category::create([
        'name' => 'Other', 'slug' => 'other', 'tenant_id' => $otherTenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $otherProduct = Product::factory()->create([
        'tenant_id' => $otherTenant->id,
        'category_id' => $otherCategory->id,
    ]);

    $otherMovement = InventoryMovement::create([
        'tenant_id' => $otherTenant->id,
        'product_id' => $otherProduct->id,
        'type' => 'purchase',
        'quantity' => 10,
        'unit_cost' => 5.00,
        'total_cost' => 50.00,
        'balance_quantity' => 10,
    ]);

    $visibleMovements = InventoryMovement::all();
    expect($visibleMovements->contains($otherMovement))->toBeFalse();
});

it('displays correct type labels', function () {
    $movement = InventoryMovement::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'purchase',
        'quantity' => 10,
        'unit_cost' => 5.00,
        'total_cost' => 50.00,
        'balance_quantity' => 10,
    ]);

    expect($movement->type_label)->toBe('Compra (Ingreso)');

    $saleMovement = InventoryMovement::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'sale',
        'quantity' => -5,
        'unit_cost' => 5.00,
        'total_cost' => -25.00,
        'balance_quantity' => 5,
    ]);

    expect($saleMovement->type_label)->toBe('Venta (Egreso)');
});

it('tracks balance quantity across movements', function () {
    InventoryMovement::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'purchase',
        'quantity' => 100,
        'unit_cost' => 10.00,
        'total_cost' => 1000.00,
        'balance_quantity' => 100,
    ]);

    $saleMovement = InventoryMovement::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'sale',
        'quantity' => -30,
        'unit_cost' => 10.00,
        'total_cost' => -300.00,
        'balance_quantity' => 70,
    ]);

    expect($saleMovement->balance_quantity)->toBe(70);
});

it('movement inherits tenant_id on creation', function () {
    $movement = InventoryMovement::create([
        'product_id' => $this->product->id,
        'type' => 'adjustment_inc',
        'quantity' => 5,
        'unit_cost' => 0,
        'total_cost' => 0,
        'balance_quantity' => 5,
    ]);

    expect($movement->tenant_id)->toBe($this->tenant->id);
});
