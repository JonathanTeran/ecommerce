<?php

use App\Enums\Module;
use App\Enums\PlanType;
use App\Filament\SuperAdmin\Resources\PlanResource\Pages\CreatePlan;
use App\Filament\SuperAdmin\Resources\PlanResource\Pages\EditPlan;
use App\Filament\SuperAdmin\Resources\PlanResource\Pages\ListPlans;
use App\Models\Plan;
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
});

it('can list plans', function () {
    Plan::create([
        'name' => 'Basico', 'slug' => 'basico', 'type' => 'basic',
        'price' => 50, 'modules' => ['products'], 'is_active' => true,
    ]);

    Livewire::test(ListPlans::class)
        ->assertSuccessful();
});

it('can create plan with all fields', function () {
    $modules = array_map(fn (Module $m) => $m->value, PlanType::Enterprise->modules());

    Livewire::test(CreatePlan::class)
        ->fillForm([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'type' => 'enterprise',
            'price' => 150,
            'max_products' => null,
            'max_users' => null,
            'modules' => $modules,
            'sort_order' => 3,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('plans', [
        'name' => 'Enterprise',
        'slug' => 'enterprise',
    ]);
});

it('can edit plan', function () {
    $plan = Plan::create([
        'name' => 'Basico', 'slug' => 'basico', 'type' => 'basic',
        'price' => 50, 'modules' => ['products'], 'is_active' => true,
    ]);

    Livewire::test(EditPlan::class, ['record' => $plan->getRouteKey()])
        ->fillForm(['price' => 75])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($plan->fresh()->price)->toBe('75.00');
});

it('rejects duplicate slug', function () {
    Plan::create([
        'name' => 'Plan A', 'slug' => 'plan-a', 'type' => 'basic',
        'price' => 50, 'modules' => ['products'], 'is_active' => true,
    ]);

    Livewire::test(CreatePlan::class)
        ->fillForm([
            'name' => 'Plan B', 'slug' => 'plan-a', 'type' => 'professional',
            'price' => 100, 'modules' => ['products'], 'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

it('can deactivate plan', function () {
    $plan = Plan::create([
        'name' => 'Pro', 'slug' => 'pro', 'type' => 'professional',
        'price' => 100, 'modules' => ['products'], 'is_active' => true,
    ]);

    Livewire::test(EditPlan::class, ['record' => $plan->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($plan->fresh()->is_active)->toBeFalse();
});

it('basic plan has correct modules', function () {
    $modules = PlanType::Basic->modules();
    $moduleValues = array_map(fn (Module $m) => $m->value, $modules);

    expect($moduleValues)
        ->toContain('products')
        ->toContain('orders')
        ->toContain('cart')
        ->not->toContain('inventory')
        ->not->toContain('sri_invoicing');
});

it('enterprise plan has all modules', function () {
    $modules = PlanType::Enterprise->modules();

    expect(count($modules))->toBe(count(Module::cases()));
});

it('null max_products means unlimited', function () {
    $plan = Plan::create([
        'name' => 'Unlimited', 'slug' => 'unlimited', 'type' => 'enterprise',
        'price' => 150, 'max_products' => null, 'max_users' => null,
        'modules' => ['products'], 'is_active' => true,
    ]);

    $tenant = Tenant::factory()->create();
    Subscription::create([
        'tenant_id' => $tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);

    expect($tenant->canAddProduct())->toBeTrue()
        ->and($tenant->canAddUser())->toBeTrue();
});

it('plan type returns correct prices', function () {
    expect(PlanType::Basic->price())->toBe(50.00)
        ->and(PlanType::Professional->price())->toBe(100.00)
        ->and(PlanType::Enterprise->price())->toBe(150.00);
});
