<?php

use App\Enums\Module;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\ListUsers;
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
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

    $this->tenantA = Tenant::factory()->create(['name' => 'Tenant A']);
    $this->tenantB = Tenant::factory()->create(['name' => 'Tenant B']);

    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenantA->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    Subscription::create([
        'tenant_id' => $this->tenantB->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);

    app()->instance('current_tenant', $this->tenantA);

    $this->adminA = User::factory()->create(['tenant_id' => $this->tenantA->id]);
    $this->adminA->assignRole('admin');

    $this->userA = User::factory()->create(['tenant_id' => $this->tenantA->id]);
    $this->userB = User::factory()->create(['tenant_id' => $this->tenantB->id]);
});

it('admin only sees users from their own tenant in UserResource', function () {
    $this->actingAs($this->adminA);

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords([$this->adminA, $this->userA])
        ->assertCanNotSeeTableRecords([$this->userB]);
});

it('admin cannot access edit page for user from another tenant', function () {
    $this->actingAs($this->adminA);

    $this->get(UserResource::getUrl('edit', ['record' => $this->userB]))
        ->assertNotFound();
});

it('CreateUser auto-assigns tenant_id for non-super-admin', function () {
    $this->actingAs($this->adminA);

    Livewire::test(\App\Filament\Resources\UserResource\Pages\CreateUser::class)
        ->fillForm([
            'name' => 'New Tenant User',
            'email' => 'newtenant@example.com',
            'password' => 'password123',
            'identification_type' => 'cedula',
            'is_active' => true,
            'newsletter_subscribed' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $newUser = User::where('email', 'newtenant@example.com')->first();
    expect($newUser)->not->toBeNull()
        ->and($newUser->tenant_id)->toBe($this->tenantA->id);
});

it('super admin can see users from all tenants', function () {
    $superAdmin = User::factory()->create(['tenant_id' => null]);
    $superAdmin->assignRole('super_admin');
    $this->actingAs($superAdmin);

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords([$this->adminA, $this->userA, $this->userB, $superAdmin]);
});
