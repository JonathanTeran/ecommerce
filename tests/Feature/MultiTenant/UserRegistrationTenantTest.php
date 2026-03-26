<?php

use App\Enums\Module;
use App\Livewire\Auth\Modals;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'test-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
});

it('user registration assigns tenant_id from current tenant', function () {
    app()->instance('current_tenant', $this->tenant);

    Livewire::test(Modals::class)
        ->set('mode', 'register')
        ->set('name', 'Test User')
        ->set('registerEmail', 'newuser@example.com')
        ->set('registerPassword', 'password123')
        ->set('registerPasswordConfirmation', 'password123')
        ->set('acceptLegalTerms', true)
        ->call('register');

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->tenant_id)->toBe($this->tenant->id);
});

it('user registration without tenant context gets null tenant_id', function () {
    app()->forgetInstance('current_tenant');

    Livewire::test(Modals::class)
        ->set('mode', 'register')
        ->set('name', 'No Tenant User')
        ->set('registerEmail', 'notenant@example.com')
        ->set('registerPassword', 'password123')
        ->set('registerPasswordConfirmation', 'password123')
        ->set('acceptLegalTerms', true)
        ->call('register');

    $user = User::where('email', 'notenant@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->tenant_id)->toBeNull();
});
