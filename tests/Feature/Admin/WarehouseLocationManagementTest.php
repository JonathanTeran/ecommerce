<?php

use App\Enums\Module;
use App\Filament\Resources\WarehouseLocationResource;
use App\Filament\Resources\WarehouseLocationResource\Pages\CreateWarehouseLocation;
use App\Filament\Resources\WarehouseLocationResource\Pages\EditWarehouseLocation;
use App\Filament\Resources\WarehouseLocationResource\Pages\ListWarehouseLocations;
use App\Models\Plan;
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
});

it('can render warehouse locations list page', function () {
    $this->get(WarehouseLocationResource::getUrl('index'))->assertSuccessful();
});

it('can render create warehouse location page', function () {
    $this->get(WarehouseLocationResource::getUrl('create'))->assertSuccessful();
});

it('can create a warehouse location', function () {
    Livewire::test(CreateWarehouseLocation::class)
        ->fillForm([
            'name' => 'Bodega Principal',
            'code' => 'BOD-001',
            'address' => 'Av. Principal 123',
            'is_default' => true,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(WarehouseLocation::where('code', 'BOD-001')
        ->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

it('can edit a warehouse location', function () {
    $location = WarehouseLocation::create([
        'name' => 'Bodega A',
        'code' => 'BOD-A',
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    Livewire::test(EditWarehouseLocation::class, ['record' => $location->getRouteKey()])
        ->fillForm(['name' => 'Bodega A Actualizada'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($location->fresh()->name)->toBe('Bodega A Actualizada');
});

it('can list warehouse locations in table', function () {
    $location = WarehouseLocation::create([
        'name' => 'Bodega Test',
        'code' => 'BOD-T',
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    Livewire::test(ListWarehouseLocations::class)
        ->assertCanSeeTableRecords([$location]);
});

it('enforces unique code', function () {
    WarehouseLocation::create([
        'name' => 'Bodega 1',
        'code' => 'UNIQUE-01',
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    Livewire::test(CreateWarehouseLocation::class)
        ->fillForm([
            'name' => 'Bodega 2',
            'code' => 'UNIQUE-01',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['code']);
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    $otherLocation = WarehouseLocation::create([
        'name' => 'Other Bodega',
        'code' => 'OTHER-01',
        'tenant_id' => $otherTenant->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    $visibleLocations = WarehouseLocation::all();
    expect($visibleLocations->contains($otherLocation))->toBeFalse();
});

it('can toggle active state', function () {
    $location = WarehouseLocation::create([
        'name' => 'Toggle Test',
        'code' => 'TOG-01',
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    Livewire::test(EditWarehouseLocation::class, ['record' => $location->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($location->fresh()->is_active)->toBeFalse();
});

it('location inherits tenant_id on creation', function () {
    $location = WarehouseLocation::create([
        'name' => 'Auto Tenant',
        'code' => 'AUTO-01',
        'is_active' => true,
        'is_default' => false,
    ]);

    expect($location->tenant_id)->toBe($this->tenant->id);
});
