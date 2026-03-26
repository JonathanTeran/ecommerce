<?php

use App\Enums\Module;
use App\Filament\Resources\StockAlertResource;
use App\Filament\Resources\StockAlertResource\Pages\ListStockAlerts;
use App\Filament\Resources\StockAlertResource\Pages\ViewStockAlert;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\StockAlert;
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
        'quantity' => 3,
        'low_stock_threshold' => 5,
    ]);
});

it('can render stock alerts list page', function () {
    $this->get(StockAlertResource::getUrl('index'))->assertSuccessful();
});

it('can list stock alerts in table', function () {
    $alert = StockAlert::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'low_stock',
        'threshold' => 5,
        'current_quantity' => 3,
        'status' => 'pending',
    ]);

    Livewire::test(ListStockAlerts::class)
        ->assertCanSeeTableRecords([$alert]);
});

it('can view stock alert details', function () {
    $alert = StockAlert::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'low_stock',
        'threshold' => 5,
        'current_quantity' => 3,
        'status' => 'pending',
    ]);

    $this->get(StockAlertResource::getUrl('view', ['record' => $alert]))
        ->assertSuccessful();
});

it('can acknowledge a stock alert', function () {
    $alert = StockAlert::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'low_stock',
        'threshold' => 5,
        'current_quantity' => 3,
        'status' => 'pending',
    ]);

    $alert->acknowledge($this->admin->id);

    expect($alert->fresh()->status)->toBe('acknowledged')
        ->and($alert->fresh()->acknowledged_by)->toBe($this->admin->id)
        ->and($alert->fresh()->acknowledged_at)->not->toBeNull();
});

it('can resolve a stock alert', function () {
    $alert = StockAlert::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'low_stock',
        'threshold' => 5,
        'current_quantity' => 3,
        'status' => 'acknowledged',
    ]);

    $alert->resolve();

    expect($alert->fresh()->status)->toBe('resolved')
        ->and($alert->fresh()->resolved_at)->not->toBeNull();
});

it('enforces tenant scoping on stock alerts', function () {
    $otherTenant = Tenant::factory()->create();
    $otherCategory = Category::create([
        'name' => 'Other', 'slug' => 'other', 'tenant_id' => $otherTenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $otherProduct = Product::factory()->create([
        'tenant_id' => $otherTenant->id,
        'category_id' => $otherCategory->id,
    ]);

    $otherAlert = StockAlert::create([
        'tenant_id' => $otherTenant->id,
        'product_id' => $otherProduct->id,
        'type' => 'out_of_stock',
        'threshold' => 0,
        'current_quantity' => 0,
        'status' => 'pending',
    ]);

    $visibleAlerts = StockAlert::all();
    expect($visibleAlerts->contains($otherAlert))->toBeFalse();
});

it('displays correct type labels', function () {
    $lowStock = StockAlert::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'low_stock',
        'threshold' => 5,
        'current_quantity' => 3,
        'status' => 'pending',
    ]);

    expect($lowStock->type_label)->toBe('Stock Bajo');

    $outOfStock = StockAlert::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'out_of_stock',
        'threshold' => 0,
        'current_quantity' => 0,
        'status' => 'pending',
    ]);

    expect($outOfStock->type_label)->toBe('Agotado');
});

it('displays correct status labels', function () {
    $alert = StockAlert::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'low_stock',
        'threshold' => 5,
        'current_quantity' => 3,
        'status' => 'pending',
    ]);

    expect($alert->status_label)->toBe('Pendiente');

    $alert->acknowledge();
    expect($alert->fresh()->status_label)->toBe('Reconocida');

    $alert->resolve();
    expect($alert->fresh()->status_label)->toBe('Resuelta');
});

it('shows navigation badge with pending count', function () {
    StockAlert::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'low_stock',
        'threshold' => 5,
        'current_quantity' => 3,
        'status' => 'pending',
    ]);

    expect(StockAlertResource::getNavigationBadge())->toBe('1');
});

it('pending scope returns only pending alerts', function () {
    StockAlert::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'low_stock',
        'threshold' => 5,
        'current_quantity' => 3,
        'status' => 'pending',
    ]);

    StockAlert::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'type' => 'out_of_stock',
        'threshold' => 0,
        'current_quantity' => 0,
        'status' => 'resolved',
    ]);

    expect(StockAlert::pending()->count())->toBe(1);
});
