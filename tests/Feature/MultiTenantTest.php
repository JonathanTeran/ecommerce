<?php

use App\Enums\Module;
use App\Enums\PlanType;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTenantWithPlan(PlanType $planType): array
{
    $plan = Plan::create([
        'name' => $planType->label(),
        'slug' => $planType->value,
        'type' => $planType->value,
        'price' => $planType->price(),
        'max_products' => $planType->maxProducts(),
        'max_users' => $planType->maxUsers(),
        'modules' => array_map(fn (Module $m) => $m->value, $planType->modules()),
    ]);

    $tenant = Tenant::create([
        'name' => 'Tenant ' . $planType->label(),
        'slug' => 'tenant-' . $planType->value,
    ]);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);
    $user->assignRole('admin');

    return [$tenant, $plan, $user];
}

beforeEach(function () {
    // Create the admin role
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
});

it('creates plans with correct modules', function () {
    $basicPlan = Plan::create([
        'name' => 'Basico',
        'slug' => 'basic',
        'type' => PlanType::Basic->value,
        'price' => 50.00,
        'max_products' => 500,
        'max_users' => 1,
        'modules' => array_map(fn (Module $m) => $m->value, PlanType::Basic->modules()),
    ]);

    expect($basicPlan->hasModule(Module::Products))->toBeTrue()
        ->and($basicPlan->hasModule(Module::Categories))->toBeTrue()
        ->and($basicPlan->hasModule(Module::Orders))->toBeTrue()
        ->and($basicPlan->hasModule(Module::Inventory))->toBeFalse()
        ->and($basicPlan->hasModule(Module::SriInvoicing))->toBeFalse()
        ->and($basicPlan->hasModule(Module::Api))->toBeFalse();
});

it('enterprise plan has all modules', function () {
    $enterprisePlan = Plan::create([
        'name' => 'Enterprise',
        'slug' => 'enterprise',
        'type' => PlanType::Enterprise->value,
        'price' => 150.00,
        'modules' => array_map(fn (Module $m) => $m->value, PlanType::Enterprise->modules()),
    ]);

    foreach (Module::cases() as $module) {
        expect($enterprisePlan->hasModule($module))->toBeTrue();
    }
});

it('tenant can check module access through plan', function () {
    [$tenant] = createTenantWithPlan(PlanType::Basic);

    expect($tenant->hasModule(Module::Products))->toBeTrue()
        ->and($tenant->hasModule(Module::Inventory))->toBeFalse()
        ->and($tenant->hasModule(Module::SriInvoicing))->toBeFalse();
});

it('tenant with professional plan has inventory access', function () {
    [$tenant] = createTenantWithPlan(PlanType::Professional);

    expect($tenant->hasModule(Module::Inventory))->toBeTrue()
        ->and($tenant->hasModule(Module::Coupons))->toBeTrue()
        ->and($tenant->hasModule(Module::Reviews))->toBeTrue()
        ->and($tenant->hasModule(Module::SriInvoicing))->toBeFalse();
});

it('tenant scopes products by tenant_id', function () {
    [$tenant1, , $user1] = createTenantWithPlan(PlanType::Basic);
    [$tenant2, , $user2] = createTenantWithPlan(PlanType::Professional);

    // Set tenant 1 as current
    app()->instance('current_tenant', $tenant1);

    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-cat-1',
        'tenant_id' => $tenant1->id,
    ]);

    Product::withoutGlobalScopes()->create([
        'name' => 'Product A',
        'slug' => 'product-a',
        'price' => 10.00,
        'quantity' => 5,
        'tenant_id' => $tenant1->id,
        'category_id' => $category->id,
    ]);

    Product::withoutGlobalScopes()->create([
        'name' => 'Product B',
        'slug' => 'product-b',
        'price' => 20.00,
        'quantity' => 3,
        'tenant_id' => $tenant2->id,
        'category_id' => $category->id,
    ]);

    // Tenant 1 should only see their product
    expect(Product::count())->toBe(1)
        ->and(Product::first()->name)->toBe('Product A');

    // Switch to tenant 2
    app()->instance('current_tenant', $tenant2);

    expect(Product::count())->toBe(1)
        ->and(Product::first()->name)->toBe('Product B');
});

it('subscription tracks active status correctly', function () {
    [$tenant, $plan] = createTenantWithPlan(PlanType::Basic);

    $subscription = $tenant->activeSubscription;

    expect($subscription)->not->toBeNull()
        ->and($subscription->isActive())->toBeTrue()
        ->and($subscription->plan->id)->toBe($plan->id);
});

it('tenant can check if it can add products within limit', function () {
    [$tenant] = createTenantWithPlan(PlanType::Basic);

    // Basic plan allows 500 products
    expect($tenant->canAddProduct())->toBeTrue();
});

it('tenant can check if it can add users within limit', function () {
    [$tenant] = createTenantWithPlan(PlanType::Basic);

    // Basic plan allows 1 user, we already created 1
    expect($tenant->canAddUser())->toBeFalse();
});

it('professional plan allows more users', function () {
    [$tenant] = createTenantWithPlan(PlanType::Professional);

    // Professional plan allows 3 users, we have 1
    expect($tenant->canAddUser())->toBeTrue();
});

it('enterprise plan has unlimited users', function () {
    [$tenant] = createTenantWithPlan(PlanType::Enterprise);

    expect($tenant->canAddUser())->toBeTrue();
});

it('plan type enum returns correct prices', function () {
    expect(PlanType::Basic->price())->toBe(50.00)
        ->and(PlanType::Professional->price())->toBe(100.00)
        ->and(PlanType::Enterprise->price())->toBe(150.00);
});

it('super admin bypasses tenant scope and sees all products', function () {
    [$tenant1] = createTenantWithPlan(PlanType::Basic);
    [$tenant2] = createTenantWithPlan(PlanType::Professional);

    $category = Category::withoutGlobalScopes()->create([
        'name' => 'Test Category',
        'slug' => 'test-cat-sa',
        'tenant_id' => $tenant1->id,
    ]);

    Product::withoutGlobalScopes()->create([
        'name' => 'Product T1',
        'slug' => 'product-t1',
        'price' => 10.00,
        'quantity' => 5,
        'tenant_id' => $tenant1->id,
        'category_id' => $category->id,
    ]);

    Product::withoutGlobalScopes()->create([
        'name' => 'Product T2',
        'slug' => 'product-t2',
        'price' => 20.00,
        'quantity' => 3,
        'tenant_id' => $tenant2->id,
        'category_id' => $category->id,
    ]);

    // Create super admin user (no tenant)
    $superAdmin = User::factory()->create(['tenant_id' => null]);
    $superAdmin->assignRole('super_admin');

    // Act as super admin
    $this->actingAs($superAdmin);

    // Super admin should see ALL products (no tenant filter)
    expect(Product::count())->toBe(2);
});

it('super admin has access to module-restricted resources', function () {
    $superAdmin = User::factory()->create(['tenant_id' => null]);
    $superAdmin->assignRole('super_admin');

    $this->actingAs($superAdmin);

    expect($superAdmin->isSuperAdmin())->toBeTrue();
});
