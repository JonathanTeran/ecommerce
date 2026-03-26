<?php

use App\Models\GeneralSetting;
use App\Models\HomepageSection;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenantA = Tenant::factory()->create(['name' => 'Store A', 'slug' => 'store-a']);
    $this->tenantB = Tenant::factory()->create(['name' => 'Store B', 'slug' => 'store-b']);

    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $this->tenantA->id,
        'site_name' => 'Tienda A',
        'theme_color' => 'red',
    ]);

    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $this->tenantB->id,
        'site_name' => 'Tienda B',
        'theme_color' => 'blue',
    ]);
});

afterEach(function () {
    app()->forgetInstance('current_tenant');
});

it('forCurrentTenant returns only the active tenant settings', function () {
    app()->instance('current_tenant', $this->tenantA);

    $settings = GeneralSetting::forCurrentTenant();

    expect($settings)->not->toBeNull();
    expect($settings->site_name)->toBe('Tienda A');
    expect($settings->theme_color)->toBe('red');
    expect($settings->tenant_id)->toBe($this->tenantA->id);
});

it('forCurrentTenant does not leak settings from another tenant', function () {
    app()->instance('current_tenant', $this->tenantB);

    $settings = GeneralSetting::forCurrentTenant();

    expect($settings)->not->toBeNull();
    expect($settings->site_name)->toBe('Tienda B');
    expect($settings->tenant_id)->toBe($this->tenantB->id);
});

it('forCurrentTenant returns null when no tenant is bound', function () {
    $settings = GeneralSetting::forCurrentTenant();

    expect($settings)->toBeNull();
});

it('forCurrentTenantOrCreate creates settings for the correct tenant', function () {
    $tenantC = Tenant::factory()->create(['name' => 'Store C', 'slug' => 'store-c']);
    app()->instance('current_tenant', $tenantC);

    $settings = GeneralSetting::forCurrentTenantOrCreate();

    expect($settings->tenant_id)->toBe($tenantC->id);
    expect($settings->site_name)->toBe(config('app.name'));
});

it('forCurrentTenantOrCreate throws when no tenant context', function () {
    GeneralSetting::forCurrentTenantOrCreate();
})->throws(\RuntimeException::class, 'No tenant context available.');

it('homepage sections are isolated per tenant', function () {
    HomepageSection::withoutGlobalScopes()->create([
        'tenant_id' => $this->tenantA->id,
        'type' => 'hero',
        'name' => 'Hero A',
        'sort_order' => 1,
        'is_active' => true,
        'config' => [],
    ]);

    HomepageSection::withoutGlobalScopes()->create([
        'tenant_id' => $this->tenantB->id,
        'type' => 'hero',
        'name' => 'Hero B',
        'sort_order' => 1,
        'is_active' => true,
        'config' => [],
    ]);

    // Tenant A should only see its own section
    app()->instance('current_tenant', $this->tenantA);
    $sectionsA = HomepageSection::active()->ordered()->get();
    expect($sectionsA)->toHaveCount(1);
    expect($sectionsA->first()->name)->toBe('Hero A');

    // Tenant B should only see its own section
    app()->instance('current_tenant', $this->tenantB);
    $sectionsB = HomepageSection::active()->ordered()->get();
    expect($sectionsB)->toHaveCount(1);
    expect($sectionsB->first()->name)->toBe('Hero B');
});

it('deleting homepage sections does not affect other tenants', function () {
    HomepageSection::withoutGlobalScopes()->create([
        'tenant_id' => $this->tenantA->id,
        'type' => 'hero',
        'name' => 'Hero A',
        'sort_order' => 1,
        'is_active' => true,
        'config' => [],
    ]);

    HomepageSection::withoutGlobalScopes()->create([
        'tenant_id' => $this->tenantB->id,
        'type' => 'hero',
        'name' => 'Hero B',
        'sort_order' => 1,
        'is_active' => true,
        'config' => [],
    ]);

    // Delete only tenant A's sections (simulating ManageHomepage::save)
    HomepageSection::withoutGlobalScopes()
        ->where('tenant_id', $this->tenantA->id)
        ->delete();

    // Tenant B's section should still exist
    $remaining = HomepageSection::withoutGlobalScopes()->get();
    expect($remaining)->toHaveCount(1);
    expect($remaining->first()->tenant_id)->toBe($this->tenantB->id);
    expect($remaining->first()->name)->toBe('Hero B');
});

it('storefront falls back to default tenant when domain not found', function () {
    $this->tenantA->update(['is_default' => true]);

    $response = $this->get('/');
    $response->assertOk();

    $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
    expect($tenant)->not->toBeNull();
    expect($tenant->id)->toBe($this->tenantA->id);
});
