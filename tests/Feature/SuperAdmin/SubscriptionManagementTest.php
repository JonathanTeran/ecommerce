<?php

use App\Enums\Module;
use App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages\CreateSubscription;
use App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages\EditSubscription;
use App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages\ListSubscriptions;
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
    Filament::setCurrentPanel(Filament::getPanel('super-admin'));
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super_admin');
    $this->actingAs($this->superAdmin);

    $this->basicPlan = Plan::create([
        'name' => 'Basico', 'slug' => 'basico', 'type' => 'basic',
        'price' => 50, 'max_products' => 500, 'max_users' => 1,
        'modules' => ['products', 'orders', 'cart'], 'is_active' => true,
    ]);

    $this->enterprisePlan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);

    $this->tenant = Tenant::factory()->create();
});

it('can create subscription', function () {
    Livewire::test(CreateSubscription::class)
        ->fillForm([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->basicPlan->id,
            'status' => 'active',
            'starts_at' => now()->format('Y-m-d H:i:s'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('subscriptions', [
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->basicPlan->id,
        'status' => 'active',
    ]);
});

it('can upgrade plan by editing subscription', function () {
    $subscription = Subscription::create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    Livewire::test(EditSubscription::class, ['record' => $subscription->getRouteKey()])
        ->fillForm(['plan_id' => $this->enterprisePlan->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($subscription->fresh()->plan_id)->toBe($this->enterprisePlan->id);
});

it('can cancel subscription', function () {
    $subscription = Subscription::create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    Livewire::test(EditSubscription::class, ['record' => $subscription->getRouteKey()])
        ->fillForm(['status' => 'cancelled'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($subscription->fresh()->status)->toBe('cancelled');
});

it('active subscription determines tenant modules', function () {
    Subscription::create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    expect($this->tenant->hasModule(Module::Products))->toBeTrue()
        ->and($this->tenant->hasModule(Module::Orders))->toBeTrue()
        ->and($this->tenant->hasModule(Module::Inventory))->toBeFalse()
        ->and($this->tenant->hasModule(Module::SriInvoicing))->toBeFalse();
});

it('upgrade grants new modules', function () {
    $subscription = Subscription::create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    expect($this->tenant->hasModule(Module::Inventory))->toBeFalse();

    $subscription->update(['plan_id' => $this->enterprisePlan->id]);
    $this->tenant->unsetRelation('activeSubscription');

    expect($this->tenant->hasModule(Module::Inventory))->toBeTrue()
        ->and($this->tenant->hasModule(Module::SriInvoicing))->toBeTrue();
});

it('cancelling subscription does not delete tenant data', function () {
    $subscription = Subscription::create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    app()->instance('current_tenant', $this->tenant);
    Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $subscription->update(['status' => 'cancelled']);

    expect(Product::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->count())->toBe(1)
        ->and($this->tenant->fresh())->not->toBeNull();
});

it('can list subscriptions', function () {
    Subscription::create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    Livewire::test(ListSubscriptions::class)->assertSuccessful();
});
