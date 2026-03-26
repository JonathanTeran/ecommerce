<?php

use App\Enums\Module;
use App\Enums\PlanType;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
});

function createPlan(PlanType $planType): Plan
{
    return Plan::create([
        'name' => $planType->label(),
        'slug' => $planType->value,
        'type' => $planType->value,
        'price' => $planType->price(),
        'max_products' => $planType->maxProducts(),
        'max_users' => $planType->maxUsers(),
        'modules' => array_map(fn (Module $m) => $m->value, $planType->modules()),
        'is_active' => true,
    ]);
}

it('provisions tenant with all dependencies', function () {
    $plan = createPlan(PlanType::Enterprise);

    $tenant = app(TenantProvisioningService::class)->provision([
        'name' => 'Test Store',
        'slug' => 'test-store',
        'domain' => 'test.example.com',
        'plan_id' => $plan->id,
        'admin_name' => 'Test Admin',
        'admin_email' => 'admin@test.com',
        'admin_password' => 'password123',
    ]);

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->and($tenant->name)->toBe('Test Store')
        ->and($tenant->slug)->toBe('test-store')
        ->and($tenant->domain)->toBe('test.example.com')
        ->and($tenant->is_active)->toBeTrue();

    // GeneralSetting created
    $settings = GeneralSetting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
    expect($settings)->not->toBeNull()
        ->and($settings->site_name)->toBe('Test Store');

    // Subscription created
    $subscription = Subscription::where('tenant_id', $tenant->id)->first();
    expect($subscription)->not->toBeNull()
        ->and($subscription->status)->toBe('active')
        ->and($subscription->plan_id)->toBe($plan->id);

    // Admin user created
    $admin = User::where('email', 'admin@test.com')->first();
    expect($admin)->not->toBeNull()
        ->and($admin->tenant_id)->toBe($tenant->id)
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull();
});

it('provisions tenant with correct plan modules', function () {
    $plan = createPlan(PlanType::Basic);

    $tenant = app(TenantProvisioningService::class)->provision([
        'name' => 'Basic Store',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin',
        'admin_email' => 'admin@basic.com',
        'admin_password' => 'password123',
    ]);

    expect($tenant->hasModule(Module::Products))->toBeTrue()
        ->and($tenant->hasModule(Module::Orders))->toBeTrue()
        ->and($tenant->hasModule(Module::Inventory))->toBeFalse()
        ->and($tenant->hasModule(Module::SriInvoicing))->toBeFalse();
});

it('fails provisioning with duplicate slug', function () {
    $plan = createPlan(PlanType::Basic);

    app(TenantProvisioningService::class)->provision([
        'name' => 'Store One',
        'slug' => 'store-one',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin 1',
        'admin_email' => 'admin1@test.com',
        'admin_password' => 'password123',
    ]);

    app(TenantProvisioningService::class)->provision([
        'name' => 'Store Duplicate',
        'slug' => 'store-one',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin 2',
        'admin_email' => 'admin2@test.com',
        'admin_password' => 'password123',
    ]);
})->throws(\Illuminate\Database\QueryException::class);

it('resolves storefront tenant by domain', function () {
    $plan = createPlan(PlanType::Enterprise);

    $tenant = app(TenantProvisioningService::class)->provision([
        'name' => 'Domain Store',
        'slug' => 'domain-store',
        'domain' => 'shop.example.com',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin',
        'admin_email' => 'admin@domain.com',
        'admin_password' => 'password123',
    ]);

    $response = $this->get('http://shop.example.com/');

    expect(app()->bound('current_tenant'))->toBeTrue()
        ->and(app('current_tenant')->id)->toBe($tenant->id);
});

it('falls back to default tenant slug', function () {
    $plan = createPlan(PlanType::Enterprise);

    $tenant = app(TenantProvisioningService::class)->provision([
        'name' => 'Fallback Store',
        'slug' => 'fallback-store',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin',
        'admin_email' => 'admin@fallback.com',
        'admin_password' => 'password123',
    ]);

    $tenant->update(['is_default' => true]);

    // Reset any previously bound tenant
    app()->forgetInstance('current_tenant');

    $response = $this->get('/');

    expect(app()->bound('current_tenant'))->toBeTrue()
        ->and(app('current_tenant')->id)->toBe($tenant->id);
});

it('child models inherit tenant_id on creation', function () {
    $plan = createPlan(PlanType::Enterprise);

    $tenant = app(TenantProvisioningService::class)->provision([
        'name' => 'Child Test',
        'slug' => 'child-test',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin',
        'admin_email' => 'admin@child.com',
        'admin_password' => 'password123',
    ]);

    app()->instance('current_tenant', $tenant);

    $category = \App\Models\Category::create([
        'name' => 'Test Cat',
        'slug' => 'test-cat',
        'tenant_id' => $tenant->id,
    ]);

    // ProductVariant inherits tenant_id
    $product = Product::withoutGlobalScopes()->create([
        'name' => 'Test Product',
        'slug' => 'test-product',
        'price' => 10.00,
        'quantity' => 5,
        'tenant_id' => $tenant->id,
        'category_id' => $category->id,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'name' => 'Size L',
        'sku' => 'TST-L',
        'price' => 10.00,
        'quantity' => 5,
    ]);

    expect($variant->tenant_id)->toBe($tenant->id);

    // OrderStatusHistory inherits tenant_id
    $order = Order::withoutGlobalScopes()->create([
        'user_id' => User::where('email', 'admin@child.com')->first()->id,
        'order_number' => 'ORD-TEST-001',
        'status' => 'pending',
        'tenant_id' => $tenant->id,
    ]);

    $history = OrderStatusHistory::create([
        'order_id' => $order->id,
        'old_status' => null,
        'new_status' => 'pending',
    ]);

    expect($history->tenant_id)->toBe($tenant->id);
});

it('seeder creates complete demo environment', function () {
    // Run the seeders in order
    $this->seed(\Database\Seeders\SuperAdminSeeder::class);
    $this->seed(\Database\Seeders\PlanSeeder::class);
    $this->seed(\Database\Seeders\DemoTenantSeeder::class);

    // Super admin exists
    $superAdmin = User::where('email', 'superadmin@amephia.com')->first();
    expect($superAdmin)->not->toBeNull()
        ->and($superAdmin->hasRole('super_admin'))->toBeTrue();

    // 3 plans exist
    expect(Plan::count())->toBe(3);

    // Demo tenant exists with admin
    $expectedSlug = strtolower(str_replace(' ', '-', config('app.name')));
    $tenant = Tenant::where('slug', $expectedSlug)->first();
    expect($tenant)->not->toBeNull()
        ->and($tenant->is_active)->toBeTrue();

    $expectedEmail = 'admin@' . $expectedSlug . '.com';
    $admin = User::where('email', $expectedEmail)->first();
    expect($admin)->not->toBeNull()
        ->and($admin->tenant_id)->toBe($tenant->id)
        ->and($admin->hasRole('admin'))->toBeTrue();

    // Subscription exists
    expect($tenant->activeSubscription)->not->toBeNull()
        ->and($tenant->activeSubscription->plan->type->value)->toBe('enterprise');

    // GeneralSetting exists
    $settings = GeneralSetting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
    expect($settings)->not->toBeNull();
});
