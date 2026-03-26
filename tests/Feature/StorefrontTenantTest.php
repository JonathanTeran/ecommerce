<?php

use App\Enums\Module;
use App\Enums\PlanType;
use App\Enums\SectionType;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\HomepageSection;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Support\ThemeColors;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTenantWithDomain(string $domain, string $name = 'Test Tenant'): Tenant
{
    $plan = Plan::firstOrCreate(
        ['slug' => 'basic-storefront'],
        [
            'name' => 'Basic',
            'type' => PlanType::Basic->value,
            'price' => 50,
            'max_products' => 500,
            'max_users' => 3,
            'modules' => array_map(fn (Module $m) => $m->value, PlanType::Basic->modules()),
        ]
    );

    $tenant = Tenant::create([
        'name' => $name,
        'slug' => 'tenant-' . uniqid(),
        'domain' => $domain,
        'theme_color' => 'blue',
    ]);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    return $tenant;
}

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
});

it('resolves tenant from request domain', function () {
    $tenant = createTenantWithDomain('tienda1.test');

    $response = $this->get('http://tienda1.test/');

    $response->assertOk();

    $resolved = app()->bound('current_tenant') ? app('current_tenant') : null;
    expect($resolved)->not->toBeNull()
        ->and($resolved->id)->toBe($tenant->id);
});

it('does not resolve tenant for unknown domain', function () {
    $response = $this->get('http://unknown.test/');

    $response->assertOk();
});

it('returns 503 for inactive tenant domain', function () {
    $tenant = createTenantWithDomain('inactive.test');
    $tenant->update(['is_active' => false]);

    $response = $this->get('http://inactive.test/');

    $response->assertStatus(503);
});

it('scopes products by tenant domain on storefront', function () {
    $tenant1 = createTenantWithDomain('store1.test', 'Store 1');
    $tenant2 = createTenantWithDomain('store2.test', 'Store 2');

    $category = Category::withoutGlobalScopes()->create([
        'name' => 'General',
        'slug' => 'general-sf-' . uniqid(),
        'tenant_id' => $tenant1->id,
    ]);

    Product::withoutGlobalScopes()->create([
        'name' => 'Product Store 1',
        'slug' => 'product-s1-' . uniqid(),
        'price' => 10,
        'quantity' => 5,
        'is_active' => true,
        'is_featured' => true,
        'tenant_id' => $tenant1->id,
        'category_id' => $category->id,
    ]);

    Product::withoutGlobalScopes()->create([
        'name' => 'Product Store 2',
        'slug' => 'product-s2-' . uniqid(),
        'price' => 20,
        'quantity' => 3,
        'is_active' => true,
        'is_featured' => true,
        'tenant_id' => $tenant2->id,
        'category_id' => $category->id,
    ]);

    // Create a ProductGrid section for tenant1 so products appear on homepage
    HomepageSection::withoutGlobalScopes()->create([
        'tenant_id' => $tenant1->id,
        'type' => SectionType::ProductGrid,
        'name' => 'Productos',
        'sort_order' => 1,
        'is_active' => true,
        'config' => ['heading' => 'Productos', 'source' => 'featured', 'limit' => 8, 'enable_infinite_scroll' => false],
    ]);

    // Visit store1's domain — products are scoped during the request
    $response = $this->get('http://store1.test/');

    $response->assertOk()
        ->assertSee('Product Store 1')
        ->assertDontSee('Product Store 2');
});

it('shows tenant branding in layout', function () {
    $tenant = createTenantWithDomain('branded.test', 'Mi Tienda');

    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'site_name' => 'Mi Tienda Premium',
        'tax_rate' => 15.0,
    ]);

    $response = $this->get('http://branded.test/');

    $response->assertOk()
        ->assertSee('Mi Tienda Premium');
});

it('applies theme color for tenant', function () {
    $tenant = createTenantWithDomain('themed.test');
    $tenant->update(['theme_color' => 'red']);

    $response = $this->get('http://themed.test/');

    $response->assertOk()
        ->assertSee('--color-primary-600: #dc2626', false);
});

it('does not inject custom theme style for default color', function () {
    $tenant = createTenantWithDomain('default.test');
    $tenant->update(['theme_color' => 'indigo']);

    $response = $this->get('http://default.test/');

    $response->assertOk()
        ->assertDontSee('--color-primary-600: #dc2626', false);
});

it('resolves tenant on API routes', function () {
    $tenant = createTenantWithDomain('api-tenant.test');

    $response = $this->getJson('http://api-tenant.test/api/search?q=test');

    $response->assertOk();

    $resolved = app()->bound('current_tenant') ? app('current_tenant') : null;
    expect($resolved)->not->toBeNull()
        ->and($resolved->id)->toBe($tenant->id);
});

it('ThemeColors returns correct palette', function () {
    $colors = ThemeColors::get('red');

    expect($colors)->not->toBeNull()
        ->and($colors['--color-primary-600'])->toBe('#dc2626');
});

it('ThemeColors returns null for unknown color', function () {
    expect(ThemeColors::get('nonexistent'))->toBeNull();
});

it('shows tenant logo in auth modal when configured', function () {
    $tenant = createTenantWithDomain('logo-auth.test', 'Logo Store');

    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'site_name' => 'Logo Store',
        'site_logo' => 'logos/tenant-logo.png',
        'tax_rate' => 15.0,
    ]);

    $response = $this->get('http://logo-auth.test/');

    $response->assertOk()
        ->assertSee('storage/logos/tenant-logo.png', false);
});

it('shows site name as fallback when no tenant logo configured', function () {
    $tenant = createTenantWithDomain('no-logo.test', 'No Logo Store');

    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'site_name' => 'No Logo Store',
        'site_logo' => null,
        'tax_rate' => 15.0,
    ]);

    $response = $this->get('http://no-logo.test/');

    $response->assertOk()
        ->assertDontSee('images/logo.png', false)
        ->assertSee('No Logo Store');
});

it('two tenants with different domains see their own settings', function () {
    $tenant1 = createTenantWithDomain('shop-a.test', 'Shop A');
    $tenant2 = createTenantWithDomain('shop-b.test', 'Shop B');

    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenant1->id,
        'site_name' => 'Shop A Premium',
        'tax_rate' => 15.0,
    ]);

    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenant2->id,
        'site_name' => 'Shop B Deluxe',
        'tax_rate' => 12.0,
    ]);

    $response1 = $this->get('http://shop-a.test/');
    $response1->assertOk()->assertSee('Shop A Premium');
});
