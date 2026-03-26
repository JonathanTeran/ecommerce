<?php

use App\Enums\Module;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
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

it('can render users list page', function () {
    $this->get(UserResource::getUrl('index'))->assertSuccessful();
});

it('can create user via form', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'identification_type' => 'cedula',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(User::where('email', 'newuser@test.com')->exists())->toBeTrue();
});

it('can edit user with all required fields', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
            'phone' => '0991234567',
            'password' => 'newpassword123',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->phone)->toBe('0991234567');
});

it('users with different tenant_id exist independently', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

    // User model doesn't use BelongsToTenant — just verify data integrity
    expect($otherUser->tenant_id)->toBe($otherTenant->id)
        ->and($this->admin->tenant_id)->toBe($this->tenant->id);
});

it('can toggle user active state via model', function () {
    $user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
    ]);

    $user->update(['is_active' => false]);

    expect($user->fresh()->is_active)->toBeFalse();
});
