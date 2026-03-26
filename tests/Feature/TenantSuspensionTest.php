<?php

use App\Enums\Module;
use App\Enums\PlanType;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
});

function createTestTenant(array $overrides = []): Tenant
{
    $plan = Plan::create([
        'name' => 'Enterprise',
        'slug' => 'enterprise',
        'type' => PlanType::Enterprise->value,
        'price' => 150,
        'modules' => array_map(fn (Module $m) => $m->value, PlanType::Enterprise->modules()),
        'is_active' => true,
    ]);

    return app(TenantProvisioningService::class)->provision(array_merge([
        'name' => 'Test Store',
        'slug' => 'test-store',
        'domain' => 'test.example.com',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin',
        'admin_email' => 'admin@test.com',
        'admin_password' => 'password123',
    ], $overrides));
}

it('blocks storefront access when tenant is suspended', function () {
    $tenant = createTestTenant();
    $tenant->update(['is_active' => false]);

    $response = $this->get('http://test.example.com/');

    $response->assertStatus(503);
    $response->assertSee('Tienda Suspendida');
});

it('shows custom suspension message on storefront', function () {
    $tenant = createTestTenant();
    $tenant->update([
        'is_active' => false,
        'suspension_message' => 'Su cuenta esta en revision por falta de pago.',
    ]);

    $response = $this->get('http://test.example.com/');

    $response->assertStatus(503);
    $response->assertSee('Su cuenta esta en revision por falta de pago.');
});

it('shows default message when no custom message is set', function () {
    $tenant = createTestTenant();
    $tenant->update(['is_active' => false]);

    $response = $this->get('http://test.example.com/');

    $response->assertStatus(503);
    $response->assertSee('Esta tienda se encuentra temporalmente suspendida.');
});

it('blocks admin panel access when tenant is suspended', function () {
    $tenant = createTestTenant();
    $admin = User::where('email', 'admin@test.com')->first();

    $tenant->update([
        'is_active' => false,
        'suspension_message' => 'Cuenta suspendida por el administrador.',
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(403);
    $response->assertSee('Tienda Suspendida');
    $response->assertSee('Cuenta suspendida por el administrador.');
});

it('allows access when tenant is active', function () {
    $tenant = createTestTenant();

    $response = $this->get('http://test.example.com/');

    $response->assertOk();
});

it('allows reactivating a suspended tenant', function () {
    $tenant = createTestTenant();
    $tenant->update(['is_active' => false, 'suspension_message' => 'Suspendida']);

    // Suspended
    $response = $this->get('http://test.example.com/');
    $response->assertStatus(503);

    // Reactivate
    $tenant->update(['is_active' => true, 'suspension_message' => null]);

    // Now works
    $response = $this->get('http://test.example.com/');
    $response->assertOk();
});
