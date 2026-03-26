<?php

use App\Enums\Module;
use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Filament\Resources\CustomerResource\Pages\ViewCustomer;
use App\Models\Address;
use App\Models\Order;
use App\Models\Plan;
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

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id, 'sub_role' => 'admin']);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render the customers list', function () {
    Livewire::test(ListCustomers::class)
        ->assertSuccessful();
});

it('shows only customers not admin users', function () {
    $customer = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'sub_role' => null,
        'name' => 'Cliente Regular',
    ]);

    $adminUser = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'sub_role' => 'admin',
        'name' => 'Admin User',
    ]);

    Livewire::test(ListCustomers::class)
        ->assertCanSeeTableRecords([$customer])
        ->assertCanNotSeeTableRecords([$adminUser]);
});

it('can view a customer', function () {
    $customer = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'sub_role' => null,
    ]);

    Livewire::test(ViewCustomer::class, ['record' => $customer->getRouteKey()])
        ->assertSuccessful();
});

it('can edit a customer', function () {
    $customer = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'sub_role' => null,
        'name' => 'Original Name',
    ]);

    Livewire::test(EditCustomer::class, ['record' => $customer->getRouteKey()])
        ->assertSuccessful()
        ->fillForm(['name' => 'Updated Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($customer->fresh()->name)->toBe('Updated Name');
});

it('has correct navigation group and icon', function () {
    expect(CustomerResource::getNavigationGroup())->toBe('Gestión de Tienda');
});

it('requires orders module for access', function () {
    expect(CustomerResource::canAccess())->toBeTrue();

    app()->forgetInstance('current_tenant');

    expect(CustomerResource::canAccess())->toBeFalse();
});

it('filters customers by tenant', function () {
    $otherTenant = Tenant::factory()->create();

    $ownCustomer = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'sub_role' => null,
        'name' => 'Mi Cliente',
    ]);

    $otherCustomer = User::factory()->create([
        'tenant_id' => $otherTenant->id,
        'sub_role' => null,
        'name' => 'Otro Cliente',
    ]);

    Livewire::test(ListCustomers::class)
        ->assertCanSeeTableRecords([$ownCustomer])
        ->assertCanNotSeeTableRecords([$otherCustomer]);
});

it('shows customer orders count in table', function () {
    $customer = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'sub_role' => null,
    ]);

    Order::factory()->count(3)->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $customer->id,
    ]);

    Livewire::test(ListCustomers::class)
        ->assertCanSeeTableRecords([$customer]);
});
