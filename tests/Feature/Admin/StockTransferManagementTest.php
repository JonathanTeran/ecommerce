<?php

use App\Enums\Module;
use App\Filament\Resources\StockTransferResource;
use App\Filament\Resources\StockTransferResource\Pages\CreateStockTransfer;
use App\Filament\Resources\StockTransferResource\Pages\EditStockTransfer;
use App\Filament\Resources\StockTransferResource\Pages\ListStockTransfers;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\StockTransfer;
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
        'quantity' => 100,
    ]);

    $this->locationA = WarehouseLocation::create([
        'name' => 'Bodega A',
        'code' => 'BOD-A',
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'is_default' => true,
    ]);

    $this->locationB = WarehouseLocation::create([
        'name' => 'Bodega B',
        'code' => 'BOD-B',
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'is_default' => false,
    ]);
});

it('can render stock transfers list page', function () {
    $this->get(StockTransferResource::getUrl('index'))->assertSuccessful();
});

it('can render create stock transfer page', function () {
    $this->get(StockTransferResource::getUrl('create'))->assertSuccessful();
});

it('can fill create stock transfer form fields', function () {
    Livewire::test(CreateStockTransfer::class)
        ->fillForm([
            'from_location_id' => $this->locationA->id,
            'to_location_id' => $this->locationB->id,
            'notes' => 'Test transfer',
        ])
        ->assertHasNoFormErrors(['from_location_id', 'to_location_id', 'notes']);
});

it('can list stock transfers in table', function () {
    $transfer = StockTransfer::create([
        'tenant_id' => $this->tenant->id,
        'from_location_id' => $this->locationA->id,
        'to_location_id' => $this->locationB->id,
        'status' => 'draft',
        'created_by' => $this->admin->id,
    ]);

    Livewire::test(ListStockTransfers::class)
        ->assertCanSeeTableRecords([$transfer]);
});

it('can render edit page for draft stock transfer', function () {
    $transfer = StockTransfer::create([
        'tenant_id' => $this->tenant->id,
        'from_location_id' => $this->locationA->id,
        'to_location_id' => $this->locationB->id,
        'status' => 'draft',
        'created_by' => $this->admin->id,
    ]);

    \App\Models\StockTransferItem::create([
        'stock_transfer_id' => $transfer->id,
        'product_id' => $this->product->id,
        'quantity' => 5,
        'tenant_id' => $this->tenant->id,
    ]);

    $this->get(StockTransferResource::getUrl('edit', ['record' => $transfer]))
        ->assertSuccessful();
});

it('auto-generates transfer number', function () {
    $transfer = StockTransfer::create([
        'tenant_id' => $this->tenant->id,
        'from_location_id' => $this->locationA->id,
        'to_location_id' => $this->locationB->id,
        'status' => 'draft',
        'created_by' => $this->admin->id,
    ]);

    expect($transfer->transfer_number)->toStartWith('TRF-');
});

it('enforces tenant scoping on stock transfers', function () {
    $otherTenant = Tenant::factory()->create();
    $otherLocationA = WarehouseLocation::create([
        'name' => 'Other A', 'code' => 'OTH-A', 'tenant_id' => $otherTenant->id,
        'is_active' => true, 'is_default' => false,
    ]);
    $otherLocationB = WarehouseLocation::create([
        'name' => 'Other B', 'code' => 'OTH-B', 'tenant_id' => $otherTenant->id,
        'is_active' => true, 'is_default' => false,
    ]);

    $otherTransfer = StockTransfer::create([
        'tenant_id' => $otherTenant->id,
        'from_location_id' => $otherLocationA->id,
        'to_location_id' => $otherLocationB->id,
        'status' => 'draft',
    ]);

    $visibleTransfers = StockTransfer::all();
    expect($visibleTransfers->contains($otherTransfer))->toBeFalse();
});

it('displays correct status labels', function () {
    $transfer = StockTransfer::create([
        'tenant_id' => $this->tenant->id,
        'from_location_id' => $this->locationA->id,
        'to_location_id' => $this->locationB->id,
        'status' => 'draft',
        'created_by' => $this->admin->id,
    ]);

    expect($transfer->status_label)->toBe('Borrador');

    $transfer->update(['status' => 'completed', 'completed_at' => now()]);
    expect($transfer->fresh()->status_label)->toBe('Completada');

    $cancelledTransfer = StockTransfer::create([
        'tenant_id' => $this->tenant->id,
        'from_location_id' => $this->locationA->id,
        'to_location_id' => $this->locationB->id,
        'status' => 'cancelled',
        'created_by' => $this->admin->id,
    ]);

    expect($cancelledTransfer->status_label)->toBe('Cancelada');
});

it('isDraft returns correct state', function () {
    $draft = StockTransfer::create([
        'tenant_id' => $this->tenant->id,
        'from_location_id' => $this->locationA->id,
        'to_location_id' => $this->locationB->id,
        'status' => 'draft',
        'created_by' => $this->admin->id,
    ]);

    expect($draft->isDraft())->toBeTrue();

    $draft->update(['status' => 'completed', 'completed_at' => now()]);
    expect($draft->fresh()->isDraft())->toBeFalse();
});

it('can cancel a draft transfer', function () {
    $transfer = StockTransfer::create([
        'tenant_id' => $this->tenant->id,
        'from_location_id' => $this->locationA->id,
        'to_location_id' => $this->locationB->id,
        'status' => 'draft',
        'created_by' => $this->admin->id,
    ]);

    $transfer->update(['status' => 'cancelled']);

    expect($transfer->fresh()->status)->toBe('cancelled');
});

it('transfer inherits tenant_id on creation', function () {
    $transfer = StockTransfer::create([
        'from_location_id' => $this->locationA->id,
        'to_location_id' => $this->locationB->id,
        'status' => 'draft',
        'created_by' => $this->admin->id,
    ]);

    expect($transfer->tenant_id)->toBe($this->tenant->id);
});
