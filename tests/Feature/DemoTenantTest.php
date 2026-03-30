<?php

use App\Enums\Module;
use App\Enums\PlanType;
use App\Enums\SectionType;
use App\Models\HomepageSection;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\DemoHomepageBuilder;
use App\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
});

function createDemoPlan(): Plan
{
    return Plan::create([
        'name' => PlanType::Enterprise->label(),
        'slug' => PlanType::Enterprise->value,
        'type' => PlanType::Enterprise->value,
        'price' => PlanType::Enterprise->price(),
        'max_products' => PlanType::Enterprise->maxProducts(),
        'max_users' => PlanType::Enterprise->maxUsers(),
        'modules' => array_map(fn (Module $m) => $m->value, PlanType::Enterprise->modules()),
        'is_active' => true,
    ]);
}

it('creates a demo tenant with 7 homepage sections', function () {
    $plan = createDemoPlan();

    $tenant = app(TenantProvisioningService::class)->provision([
        'name' => 'Demo Store',
        'slug' => 'demo-store',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin',
        'admin_email' => 'admin@demo-store.com',
        'admin_password' => 'password123',
        'is_demo' => true,
    ]);

    expect($tenant->is_demo)->toBeTrue();

    $sections = HomepageSection::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->get();

    expect($sections)->toHaveCount(10);
});

it('creates homepage sections for all tenants including non-demo', function () {
    $plan = createDemoPlan();

    $tenant = app(TenantProvisioningService::class)->provision([
        'name' => 'Regular Store',
        'slug' => 'regular-store',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin',
        'admin_email' => 'admin@regular.com',
        'admin_password' => 'password123',
        'is_demo' => false,
        'seed_demo_data' => false,
    ]);

    expect($tenant->is_demo)->toBeFalse();

    $sections = HomepageSection::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->get();

    expect($sections)->toHaveCount(10);
});

it('creates all section types for demo tenant', function () {
    $plan = createDemoPlan();

    $tenant = app(TenantProvisioningService::class)->provision([
        'name' => 'Full Demo',
        'slug' => 'full-demo',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin',
        'admin_email' => 'admin@full-demo.com',
        'admin_password' => 'password123',
        'is_demo' => true,
    ]);

    $sections = HomepageSection::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->get();

    $types = $sections->pluck('type')->map(fn ($t) => $t->value)->sort()->values();
    $expectedTypes = collect(SectionType::cases())->map(fn ($t) => $t->value)->sort()->values();

    expect($types->toArray())->toBe($expectedTypes->toArray());
});

it('sets correct sort order on demo sections', function () {
    $plan = createDemoPlan();

    $tenant = app(TenantProvisioningService::class)->provision([
        'name' => 'Sorted Demo',
        'slug' => 'sorted-demo',
        'plan_id' => $plan->id,
        'admin_name' => 'Admin',
        'admin_email' => 'admin@sorted-demo.com',
        'admin_password' => 'password123',
        'is_demo' => true,
    ]);

    $sections = HomepageSection::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->orderBy('sort_order')
        ->get();

    expect($sections->pluck('sort_order')->toArray())->toBe([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
    expect($sections->every(fn ($s) => $s->is_active))->toBeTrue();
});

it('sets attractive styles on demo sections', function () {
    $tenant = Tenant::factory()->create();

    app(DemoHomepageBuilder::class)->build($tenant);

    $sections = HomepageSection::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->get()
        ->keyBy(fn ($s) => $s->type->value);

    // Hero has gradient style
    $heroStyle = $sections['hero']->config['style'];
    expect($heroStyle['bg_type'])->toBe('gradient')
        ->and($heroStyle['bg_gradient_from'])->toBe('#1e1b4b')
        ->and($heroStyle['bg_gradient_to'])->toBe('#312e81')
        ->and($heroStyle['font_family'])->toBe('Poppins');

    // CTA strip has gradient
    $ctaStyle = $sections['cta_strip']->config['style'];
    expect($ctaStyle['bg_type'])->toBe('gradient')
        ->and($ctaStyle['bg_gradient_from'])->toBe('#4f46e5');

    // ValueProps has dark gradient and Poppins
    $vpStyle = $sections['value_props']->config['style'];
    expect($vpStyle['bg_type'])->toBe('gradient')
        ->and($vpStyle['bg_gradient_from'])->toBe('#18181b')
        ->and($vpStyle['font_family'])->toBe('Poppins');

    // ValueProps has 4 items
    expect($sections['value_props']->config['items'])->toHaveCount(4);

    // PromoBanners has 2 banners
    expect($sections['promo_banners']->config['banners'])->toHaveCount(2);
});
