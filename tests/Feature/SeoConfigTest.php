<?php

use App\Enums\Module;
use App\Filament\Pages\ManageStorefrontDesign;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
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

    $this->settings = GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'SEO Test Store',
        'tax_rate' => 15.00,
    ]);
});

// ========================================
// ADMIN SEO CONFIG
// ========================================

it('can save SEO config from admin', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $seoData = [
        'home_meta_title' => 'Mejor Tienda Online Ecuador',
        'home_meta_description' => 'Compra los mejores productos con envio gratis a todo el pais.',
        'home_meta_keywords' => 'tienda online, ecuador, envio gratis',
        'org_name' => 'Mi Empresa S.A.',
        'org_phone' => '+593212345678',
        'org_email' => 'info@miempresa.com',
        'org_city' => 'Quito',
        'org_country' => 'Ecuador',
        'twitter_handle' => 'mitienda',
        'google_analytics_id' => 'G-TEST123',
        'google_site_verification' => 'abc123verification',
        'robots_index' => true,
        'robots_follow' => true,
    ];

    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.seo_config', $seoData)
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->seo_config['home_meta_title'])->toBe('Mejor Tienda Online Ecuador')
        ->and($settings->seo_config['org_name'])->toBe('Mi Empresa S.A.')
        ->and($settings->seo_config['google_analytics_id'])->toBe('G-TEST123');
});

// ========================================
// FRONTEND SEO RENDERING
// ========================================

it('home page renders custom SEO meta title and description', function () {
    $this->settings->update([
        'seo_config' => [
            'home_meta_title' => 'Tienda Premium | Envio Gratis Ecuador',
            'home_meta_description' => 'Descubre productos premium con envio gratis.',
            'home_meta_keywords' => 'premium, envio gratis, ecuador',
        ],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('<title>Tienda Premium | Envio Gratis Ecuador</title>', false);
    $response->assertSee('content="Descubre productos premium con envio gratis."', false);
    $response->assertSee('content="premium, envio gratis, ecuador"', false);
});

it('home page renders Organization schema when configured', function () {
    $this->settings->update([
        'seo_config' => [
            'org_name' => 'TechStore Ecuador',
            'org_phone' => '+593999888777',
            'org_email' => 'info@techstore.ec',
            'org_city' => 'Guayaquil',
            'org_country' => 'Ecuador',
        ],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('schema.org', false);
    $response->assertSee('TechStore Ecuador', false);
    $response->assertSee('+593999888777', false);
});

it('home page renders WebSite schema with SearchAction', function () {
    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('SearchAction', false);
    $response->assertSee('search_term_string', false);
});

it('renders Twitter Card meta tags', function () {
    $this->settings->update([
        'seo_config' => [
            'twitter_handle' => 'mitienda',
            'home_meta_title' => 'Test Title',
            'home_meta_description' => 'Test Description',
        ],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('twitter:card', false);
    $response->assertSee('twitter:site', false);
    $response->assertSee('@mitienda', false);
});

it('renders Google Analytics script when configured', function () {
    $this->settings->update([
        'seo_config' => ['google_analytics_id' => 'G-TESTID123'],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('G-TESTID123', false);
    $response->assertSee('googletagmanager.com/gtag', false);
});

it('renders Facebook Pixel when configured', function () {
    $this->settings->update([
        'seo_config' => ['facebook_pixel_id' => '123456789'],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('123456789', false);
    $response->assertSee('fbevents.js', false);
});

it('renders Google site verification meta tag', function () {
    $this->settings->update([
        'seo_config' => ['google_site_verification' => 'verify_abc123'],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('google-site-verification', false);
    $response->assertSee('verify_abc123', false);
});

it('renders robots noindex when disabled', function () {
    $this->settings->update([
        'seo_config' => ['robots_index' => false, 'robots_follow' => false],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('content="noindex, nofollow"', false);
});

it('renders canonical URL', function () {
    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('rel="canonical"', false);
});

it('model returns SEO defaults when config is null', function () {
    $config = $this->settings->getSeoConfig();
    expect($config['robots_index'])->toBeTrue()
        ->and($config['robots_follow'])->toBeTrue()
        ->and($config['home_meta_title'])->toBe('')
        ->and($config['google_analytics_id'])->toBe('');
});

// ========================================
// TIKTOK PIXEL
// ========================================

it('renders TikTok Pixel when configured', function () {
    $this->settings->update([
        'seo_config' => ['tiktok_pixel_id' => 'CTTEST123456'],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('CTTEST123456', false);
    $response->assertSee('analytics.tiktok.com', false);
    $response->assertSee('ttq.page()', false);
});

it('does not render TikTok Pixel when not configured', function () {
    $this->settings->update([
        'seo_config' => ['tiktok_pixel_id' => ''],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertDontSee('analytics.tiktok.com', false);
});

it('model returns tiktok_pixel_id default when config is null', function () {
    $config = $this->settings->getSeoConfig();
    expect($config['tiktok_pixel_id'])->toBe('');
});

// ========================================
// TRACKING CONFIG (window.__TRACKING__)
// ========================================

it('renders tracking config with correct pixel flags', function () {
    $this->settings->update([
        'seo_config' => [
            'facebook_pixel_id' => '111222333',
            'tiktok_pixel_id' => 'TTTEST789',
            'google_tag_manager_id' => 'GTM-TEST1',
            'google_analytics_id' => 'G-TEST999',
        ],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('window.__TRACKING__', false);
    $response->assertSee('hasFbq: true', false);
    $response->assertSee('hasTtq: true', false);
    $response->assertSee('hasGtm: true', false);
    $response->assertSee('hasGa4: true', false);
});

it('renders tracking config with false flags when pixels are not configured', function () {
    $this->settings->update([
        'seo_config' => [],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('hasFbq: false', false);
    $response->assertSee('hasTtq: false', false);
});

// ========================================
// DATALAYER: VIEW_ITEM EVENT
// ========================================

it('renders view_item tracking event on product page', function () {
    $product = \App\Models\Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Laptop Test Tracking',
        'price' => 999.99,
    ]);

    $response = $this->get(route('products.show', $product));
    $response->assertSuccessful();
    $response->assertSee("trackEcommerce('view_item'", false);
    $response->assertSee('Laptop Test Tracking', false);
    $response->assertSee('999.99', false);
});

// ========================================
// DATALAYER: PURCHASE EVENT
// ========================================

it('renders purchase tracking event on order confirmation', function () {
    $user = \App\Models\User::factory()->create(['tenant_id' => $this->tenant->id]);
    $order = \App\Models\Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $user->id,
        'payment_method' => \App\Enums\PaymentMethod::BANK_TRANSFER,
        'order_number' => 'ORD-TRACKTEST1',
        'total' => 150.00,
        'subtotal' => 130.43,
        'tax_amount' => 19.57,
        'shipping_amount' => 0,
    ]);

    $product = \App\Models\Product::factory()->create(['tenant_id' => $this->tenant->id]);
    \App\Models\OrderItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'name' => 'Test Product Tracking',
        'price' => 130.43,
        'quantity' => 1,
        'subtotal' => 130.43,
    ]);

    $response = $this->actingAs($user)->get(route('checkout.confirmation', $order));
    $response->assertSuccessful();
    $response->assertSee("trackEcommerce('purchase'", false);
    $response->assertSee('ORD-TRACKTEST1', false);
    $response->assertSee('Test Product Tracking', false);
});

// ========================================
// GTM CONTAINER
// ========================================

it('renders Google Tag Manager script when configured', function () {
    $this->settings->update([
        'seo_config' => ['google_tag_manager_id' => 'GTM-TESTXYZ'],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('GTM-TESTXYZ', false);
    $response->assertSee('googletagmanager.com/gtm.js', false);
});

it('renders GTM noscript iframe when configured', function () {
    $this->settings->update([
        'seo_config' => ['google_tag_manager_id' => 'GTM-NOSCRIPT1'],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('googletagmanager.com/ns.html?id=GTM-NOSCRIPT1', false);
    $response->assertSee('<noscript>', false);
});

it('does not render GTM when not configured', function () {
    $this->settings->update([
        'seo_config' => ['google_tag_manager_id' => ''],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertDontSee('googletagmanager.com/gtm.js', false);
    $response->assertDontSee('googletagmanager.com/ns.html', false);
});

it('does not render GA4 when not configured', function () {
    $this->settings->update([
        'seo_config' => ['google_analytics_id' => ''],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertDontSee('googletagmanager.com/gtag', false);
});

it('does not render Facebook Pixel when not configured', function () {
    $this->settings->update([
        'seo_config' => ['facebook_pixel_id' => ''],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertDontSee('fbevents.js', false);
});

// ========================================
// DATALAYER: BEGIN_CHECKOUT EVENT
// ========================================

it('renders begin_checkout tracking event on checkout page', function () {
    $this->settings->update([
        'payment_gateways_config' => [
            'bank_transfer_enabled' => true,
            'bank_transfer_instructions' => 'Cuenta: 123456',
        ],
    ]);

    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($user)->get('/checkout');
    $response->assertSuccessful();
    $response->assertSee("trackEcommerce('begin_checkout'", false);
});

// ========================================
// CURRENCY IN TRACKING CONFIG
// ========================================

it('renders tenant currency in tracking config', function () {
    $this->settings->update([
        'currency_code' => 'EUR',
        'seo_config' => ['google_analytics_id' => 'G-CURR1'],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('window.__TRACKING__', false);
    $response->assertSee('"EUR"', false);
});

// ========================================
// TENANT ISOLATION
// ========================================

it('isolates tracking IDs between tenants', function () {
    // Configure Tenant A (current) with specific tracking IDs
    $this->settings->update([
        'seo_config' => [
            'google_analytics_id' => 'G-TENANT-A111',
            'google_tag_manager_id' => 'GTM-TENANT-A',
            'facebook_pixel_id' => 'FB-TENANT-A',
            'tiktok_pixel_id' => 'TT-TENANT-A',
        ],
    ]);

    // Create Tenant B with different tracking IDs
    $tenantB = Tenant::factory()->create();
    $plan = Plan::create([
        'name' => 'Enterprise B', 'slug' => 'enterprise-b', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $tenantB->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenantB->id,
        'site_name' => 'Tenant B Store',
        'tax_rate' => 12.00,
        'seo_config' => [
            'google_analytics_id' => 'G-TENANT-B222',
            'google_tag_manager_id' => 'GTM-TENANT-B',
            'facebook_pixel_id' => 'FB-TENANT-B',
            'tiktok_pixel_id' => 'TT-TENANT-B',
        ],
    ]);

    // Visit as Tenant A — should see Tenant A's IDs only
    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('G-TENANT-A111', false);
    $response->assertSee('GTM-TENANT-A', false);
    $response->assertSee('FB-TENANT-A', false);
    $response->assertSee('TT-TENANT-A', false);

    // Must NOT see Tenant B's IDs
    $response->assertDontSee('G-TENANT-B222', false);
    $response->assertDontSee('GTM-TENANT-B', false);
    $response->assertDontSee('FB-TENANT-B', false);
    $response->assertDontSee('TT-TENANT-B', false);
});

it('tenant B sees only its own tracking IDs', function () {
    // Tenant A config
    $this->settings->update([
        'seo_config' => [
            'google_analytics_id' => 'G-ONLY-A',
            'facebook_pixel_id' => 'FB-ONLY-A',
        ],
    ]);

    // Create Tenant B
    $tenantB = Tenant::factory()->create();
    $plan = Plan::create([
        'name' => 'Enterprise B2', 'slug' => 'enterprise-b2', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $tenantB->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenantB->id,
        'site_name' => 'Tenant B2 Store',
        'tax_rate' => 12.00,
        'seo_config' => [
            'google_analytics_id' => 'G-ONLY-B',
            'facebook_pixel_id' => 'FB-ONLY-B',
        ],
    ]);

    // Switch to Tenant B context
    app()->instance('current_tenant', $tenantB);
    cache()->flush();

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('G-ONLY-B', false);
    $response->assertSee('FB-ONLY-B', false);

    $response->assertDontSee('G-ONLY-A', false);
    $response->assertDontSee('FB-ONLY-A', false);
});

it('seo_config is stored per tenant in database', function () {
    $this->settings->update([
        'seo_config' => ['google_analytics_id' => 'G-DB-TENANT-A'],
    ]);

    // Create another tenant with different config
    $tenantB = Tenant::factory()->create();
    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenantB->id,
        'site_name' => 'DB Tenant B',
        'tax_rate' => 12.00,
        'seo_config' => ['google_analytics_id' => 'G-DB-TENANT-B'],
    ]);

    // Verify each tenant has its own config in the database
    $settingsA = GeneralSetting::first();
    expect($settingsA->seo_config['google_analytics_id'])->toBe('G-DB-TENANT-A');

    app()->instance('current_tenant', $tenantB);
    $settingsB = GeneralSetting::first();
    expect($settingsB->seo_config['google_analytics_id'])->toBe('G-DB-TENANT-B');

    // Restore original tenant
    app()->instance('current_tenant', $this->tenant);
});

// ========================================
// DATALAYER: COMPLETE EVENT SCHEMA
// ========================================

it('view_item event includes category and brand', function () {
    $category = \App\Models\Category::firstOrCreate(
        ['slug' => 'track-cat'],
        ['name' => 'Tracking Category']
    );
    $brand = \App\Models\Brand::firstOrCreate(
        ['slug' => 'track-brand'],
        ['name' => 'Tracking Brand']
    );

    $product = \App\Models\Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Branded Product',
        'price' => 49.99,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $response = $this->get(route('products.show', $product));
    $response->assertSuccessful();
    $response->assertSee('Tracking Category', false);
    $response->assertSee('Tracking Brand', false);
    $response->assertSee('49.99', false);
});

it('purchase event includes tax and shipping breakdown', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $order = \App\Models\Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $user->id,
        'payment_method' => \App\Enums\PaymentMethod::BANK_TRANSFER,
        'order_number' => 'ORD-TAXSHIP1',
        'total' => 200.00,
        'subtotal' => 165.22,
        'tax_amount' => 24.78,
        'shipping_amount' => 10.00,
    ]);

    $product = \App\Models\Product::factory()->create(['tenant_id' => $this->tenant->id]);
    \App\Models\OrderItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'name' => 'Tax Ship Product',
        'price' => 165.22,
        'quantity' => 1,
        'subtotal' => 165.22,
    ]);

    $response = $this->actingAs($user)->get(route('checkout.confirmation', $order));
    $response->assertSuccessful();
    $response->assertSee('transaction_id', false);
    $response->assertSee('ORD-TAXSHIP1', false);
    $response->assertSee('tax:', false);
    $response->assertSee('24.78', false);
    $response->assertSee('shipping:', false);
    $response->assertSee('10', false);
});

// ========================================
// ADMIN: SAVE TRACKING IDS
// ========================================

it('can save all tracking IDs from admin', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(\App\Filament\Pages\ManageStorefrontDesign::class)
        ->set('data.seo_config', [
            'google_analytics_id' => 'G-ADMIN-TEST',
            'google_tag_manager_id' => 'GTM-ADMIN-TEST',
            'facebook_pixel_id' => '999888777666',
            'tiktok_pixel_id' => 'TTADMIN123',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->seo_config['google_analytics_id'])->toBe('G-ADMIN-TEST')
        ->and($settings->seo_config['google_tag_manager_id'])->toBe('GTM-ADMIN-TEST')
        ->and($settings->seo_config['facebook_pixel_id'])->toBe('999888777666')
        ->and($settings->seo_config['tiktok_pixel_id'])->toBe('TTADMIN123');
});
