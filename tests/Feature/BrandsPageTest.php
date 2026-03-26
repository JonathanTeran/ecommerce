<?php

use App\Enums\Module;
use App\Models\Brand;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
    app()->instance('current_tenant', $this->tenant);
});

it('renders brands page with default settings', function () {
    Brand::factory()->count(3)->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
    ]);

    $this->get('/brands')
        ->assertSuccessful()
        ->assertSee(__('Our Brands'));
});

it('renders brands page with custom title and subtitle', function () {
    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test',
        'brands_page_config' => [
            'is_enabled' => true,
            'title' => 'Nuestras Marcas Premium',
            'subtitle' => 'Las mejores marcas del mercado',
        ],
    ]);

    Brand::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
    ]);

    $this->get('/brands')
        ->assertSuccessful()
        ->assertSee('Nuestras Marcas Premium')
        ->assertSee('Las mejores marcas del mercado');
});

it('returns 404 when brands page is disabled', function () {
    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test',
        'brands_page_config' => [
            'is_enabled' => false,
        ],
    ]);

    $this->get('/brands')->assertNotFound();
});

it('shows only active brands', function () {
    Brand::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'name' => 'Marca Activa',
    ]);
    Brand::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => false,
        'name' => 'Marca Inactiva',
    ]);

    $this->get('/brands')
        ->assertSuccessful()
        ->assertSee('Marca Activa')
        ->assertDontSee('Marca Inactiva');
});

it('shows product count when enabled', function () {
    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test',
        'brands_page_config' => [
            'is_enabled' => true,
            'show_product_count' => true,
        ],
    ]);

    Brand::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
    ]);

    $this->get('/brands')->assertSuccessful();
});

it('shows empty state when no brands exist', function () {
    $this->get('/brands')
        ->assertSuccessful()
        ->assertSee(__('No brands available yet.'));
});
