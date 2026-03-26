<?php

use App\Enums\Module;
use App\Enums\PlanType;
use App\Filament\Pages\InventoryDashboard;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WarehouseLocation;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createProductForInventory(array $overrides = []): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'test-inv-cat'],
        ['name' => 'Test Category']
    );

    return Product::create(array_merge([
        'name' => 'Test Product',
        'slug' => 'test-product-' . uniqid(),
        'sku' => 'INV-' . strtoupper(uniqid()),
        'price' => 100,
        'cost' => 0,
        'quantity' => 0,
        'low_stock_threshold' => 5,
        'category_id' => $category->id,
    ], $overrides));
}

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
});

it('calculates weighted average cost on purchase', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = createProductForInventory(['quantity' => 10, 'cost' => 50.00]);

    $service = new InventoryService;
    $service->addStock($product, 10, 100.00, 'purchase', null, $user->id);

    // Weighted avg: (10*50 + 10*100) / 20 = 1500/20 = 75.00
    expect((float) $product->fresh()->cost)->toBe(75.00)
        ->and($product->fresh()->quantity)->toBe(20);
});

it('creates stock alert when product goes below threshold', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = createProductForInventory(['quantity' => 10, 'cost' => 50.00]);

    $service = new InventoryService;
    $service->removeStock($product, 7, 'sale', null, $user->id);

    // Product now has 3, threshold is 5 → low_stock alert
    expect($product->fresh()->quantity)->toBe(3);

    $alert = StockAlert::where('product_id', $product->id)->first();
    expect($alert)->not->toBeNull()
        ->and($alert->type)->toBe('low_stock')
        ->and($alert->status)->toBe('pending')
        ->and($alert->current_quantity)->toBe(3);
});

it('creates out_of_stock alert when product reaches zero', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = createProductForInventory(['quantity' => 3, 'cost' => 50.00]);

    $service = new InventoryService;
    $service->removeStock($product, 3, 'sale', null, $user->id);

    expect($product->fresh()->quantity)->toBe(0);

    $alert = StockAlert::where('product_id', $product->id)
        ->where('type', 'out_of_stock')
        ->first();
    expect($alert)->not->toBeNull()
        ->and($alert->status)->toBe('pending');
});

it('resolves alerts when stock is replenished', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = createProductForInventory(['quantity' => 3, 'cost' => 50.00]);

    $service = new InventoryService;
    // Trigger low_stock alert
    $service->removeStock($product, 1, 'sale', null, $user->id);

    $alert = StockAlert::where('product_id', $product->id)->pending()->first();
    expect($alert)->not->toBeNull();

    // Replenish stock above threshold
    $service->addStock($product, 10, 50.00, 'purchase', null, $user->id);

    expect($alert->fresh()->status)->toBe('resolved');
});

it('can acknowledge and resolve stock alerts', function () {
    $user = User::factory()->create();

    $product = createProductForInventory(['quantity' => 2]);

    $alert = StockAlert::create([
        'product_id' => $product->id,
        'type' => 'low_stock',
        'threshold' => 5,
        'current_quantity' => 2,
        'status' => 'pending',
    ]);

    $alert->acknowledge($user->id);
    expect($alert->fresh()->status)->toBe('acknowledged')
        ->and($alert->fresh()->acknowledged_by)->toBe($user->id)
        ->and($alert->fresh()->acknowledged_at)->not->toBeNull();

    $alert->resolve();
    expect($alert->fresh()->status)->toBe('resolved')
        ->and($alert->fresh()->resolved_at)->not->toBeNull();
});

it('processes stock transfer between locations', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = createProductForInventory(['quantity' => 20, 'cost' => 50.00]);

    $locationA = WarehouseLocation::create(['name' => 'Bodega A', 'code' => 'BOD-A']);
    $locationB = WarehouseLocation::create(['name' => 'Bodega B', 'code' => 'BOD-B']);

    $transfer = StockTransfer::create([
        'from_location_id' => $locationA->id,
        'to_location_id' => $locationB->id,
        'status' => 'draft',
    ]);

    StockTransferItem::create([
        'stock_transfer_id' => $transfer->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $service = new InventoryService;
    $service->processTransfer($transfer);

    // Stock unchanged overall (moved between locations)
    expect($product->fresh()->quantity)->toBe(20)
        ->and($transfer->fresh()->status)->toBe('completed')
        ->and($transfer->fresh()->completed_at)->not->toBeNull();

    // Verify movements created
    $movements = $product->inventoryMovements()->orderBy('id')->get();
    $transferOut = $movements->firstWhere('type', 'transfer_out');
    $transferIn = $movements->firstWhere('type', 'transfer_in');

    expect($transferOut)->not->toBeNull()
        ->and($transferOut->quantity)->toBe(-5)
        ->and($transferOut->warehouse_location_id)->toBe($locationA->id)
        ->and($transferIn)->not->toBeNull()
        ->and($transferIn->quantity)->toBe(5)
        ->and($transferIn->warehouse_location_id)->toBe($locationB->id);
});

it('saves notes in inventory movement', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = createProductForInventory(['quantity' => 0]);

    $service = new InventoryService;
    $movement = $service->addStock($product, 5, 10.00, 'purchase', null, $user->id, notes: 'Compra proveedor ABC');

    expect($movement->notes)->toBe('Compra proveedor ABC');
});

it('saves warehouse location in inventory movement', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = createProductForInventory(['quantity' => 0]);
    $location = WarehouseLocation::create(['name' => 'Bodega Principal', 'code' => 'BOD-001']);

    $service = new InventoryService;
    $movement = $service->addStock($product, 5, 10.00, 'purchase', null, $user->id, warehouseLocationId: $location->id);

    expect($movement->warehouse_location_id)->toBe($location->id);
});

it('inventory dashboard is accessible with Inventory module', function () {
    $plan = Plan::create([
        'name' => 'Professional',
        'slug' => 'professional-inv-' . uniqid(),
        'type' => PlanType::Professional->value,
        'price' => 100,
        'modules' => array_map(fn (Module $m) => $m->value, PlanType::Professional->modules()),
    ]);

    $tenant = Tenant::create(['name' => 'Test Tenant', 'slug' => 'inv-tenant-' . uniqid()]);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $user->assignRole('admin');

    app()->instance('current_tenant', $tenant);
    $this->actingAs($user);

    expect($tenant->hasModule(Module::Inventory))->toBeTrue()
        ->and(InventoryDashboard::canAccess())->toBeTrue();
});

it('inventory dashboard is NOT accessible without Inventory module', function () {
    $plan = Plan::create([
        'name' => 'Basic',
        'slug' => 'basic-inv-' . uniqid(),
        'type' => PlanType::Basic->value,
        'price' => 50,
        'modules' => array_map(fn (Module $m) => $m->value, PlanType::Basic->modules()),
    ]);

    $tenant = Tenant::create(['name' => 'Test Tenant', 'slug' => 'inv-basic-' . uniqid()]);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $user->assignRole('admin');

    app()->instance('current_tenant', $tenant);
    $this->actingAs($user);

    expect($tenant->hasModule(Module::Inventory))->toBeFalse()
        ->and(InventoryDashboard::canAccess())->toBeFalse();
});

it('super admin can access inventory dashboard', function () {
    $superAdmin = User::factory()->create(['tenant_id' => null]);
    $superAdmin->assignRole('super_admin');

    $this->actingAs($superAdmin);

    expect(InventoryDashboard::canAccess())->toBeTrue();
});
