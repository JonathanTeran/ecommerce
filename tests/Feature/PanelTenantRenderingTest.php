<?php

use App\Enums\Module;
use App\Enums\PlanType;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function createTenantWithBranding(string $domain, string $siteName): Tenant
{
    $plan = Plan::firstOrCreate(
        ['slug' => PlanType::Enterprise->value],
        [
            'name' => 'Enterprise',
            'type' => PlanType::Enterprise->value,
            'price' => PlanType::Enterprise->price(),
            'modules' => array_map(fn (Module $module): string => $module->value, Module::cases()),
            'is_active' => true,
        ]
    );

    $tenant = Tenant::create([
        'name' => $siteName,
        'slug' => Str::slug($siteName).'-'.Str::lower(Str::random(6)),
        'domain' => $domain,
        'is_active' => true,
    ]);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'site_name' => $siteName,
        'tax_rate' => 15.0,
    ]);

    return $tenant;
}

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'super_admin']);
    app()->instance('current_tenant', null);
});

it('renders tenant branding on the admin login page based on the request domain', function () {
    createTenantWithBranding('admin-tenant.test', 'Admin Tenant Store');
    createTenantWithBranding('other-admin.test', 'Other Admin Store');

    app()->instance('current_tenant', null);

    $response = $this->get('http://admin-tenant.test/admin/login');

    $response->assertOk()
        ->assertSee('Admin Tenant Store')
        ->assertDontSee('Other Admin Store');
});

it('renders tenant branding on the buyer login page based on the request domain', function () {
    createTenantWithBranding('buyer-tenant.test', 'Buyer Tenant Store');
    createTenantWithBranding('other-buyer.test', 'Other Buyer Store');

    app()->instance('current_tenant', null);

    $response = $this->get('http://buyer-tenant.test/buyer/login');

    $response->assertOk()
        ->assertSee('Buyer Tenant Store')
        ->assertDontSee('Other Buyer Store');
});

it('renders the admin dashboard using the authenticated admin tenant instead of the host tenant', function () {
    $adminTenant = createTenantWithBranding('tenant-a.test', 'Tenant A Admin');
    createTenantWithBranding('tenant-b.test', 'Tenant B Admin');

    $admin = User::factory()->create([
        'tenant_id' => $adminTenant->id,
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('admin');

    app()->instance('current_tenant', null);

    $response = $this->actingAs($admin)->get('http://tenant-b.test/admin');

    $response->assertOk()
        ->assertSee('Tenant A Admin')
        ->assertDontSee('Tenant B Admin');

    expect(app('current_tenant')->id)->toBe($adminTenant->id);
});
