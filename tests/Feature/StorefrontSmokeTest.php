<?php

use App\Enums\Module;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $this->plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);

    $this->settings = GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Smoke Test Store',
        'tax_rate' => 15.00,
    ]);

    $this->category = Category::create([
        'name' => 'Electronics', 'slug' => 'electronics', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => true, 'position' => 0,
    ]);

    $this->brand = Brand::create([
        'name' => 'TestBrand', 'slug' => 'testbrand', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
});

it('renders homepage without errors', function () {
    $this->get('/')->assertSuccessful();
});

it('renders shop page without errors', function () {
    $this->get('/shop')->assertSuccessful();
});

it('renders shop page with default storefront config', function () {
    $this->settings->update([
        'navbar_config' => ['show_search' => true, 'show_categories_button' => true, 'style' => 'transparent_on_scroll', 'menu_items' => []],
        'footer_config' => ['brand_description' => 'Test description', 'columns' => [], 'show_newsletter' => true, 'copyright_text' => '', 'show_payment_icons' => false],
        'shop_page_config' => ['products_per_page' => 12, 'grid_columns' => 3, 'show_brand_filter' => true, 'show_category_filter' => true],
    ]);

    $this->get('/shop')->assertSuccessful();
});

it('renders shop page with custom navbar and footer', function () {
    $this->settings->update([
        'navbar_config' => [
            'show_search' => false,
            'show_categories_button' => false,
            'style' => 'solid_white',
            'menu_items' => [
                ['label' => 'Inicio', 'url' => '/', 'open_in_new_tab' => false, 'is_visible' => true],
                ['label' => 'Tienda', 'url' => '/shop', 'open_in_new_tab' => false, 'is_visible' => true],
            ],
        ],
        'footer_config' => [
            'brand_description' => 'Custom brand description',
            'columns' => [
                ['title' => 'Links', 'links' => [['label' => 'Home', 'url' => '/']]],
            ],
            'show_newsletter' => false,
            'copyright_text' => '2026 Custom Copyright',
            'show_payment_icons' => true,
        ],
        'social_links' => [
            'facebook_url' => 'https://facebook.com/test',
            'instagram_url' => 'https://instagram.com/test',
            'whatsapp_number' => '+593999999999',
        ],
    ]);

    $response = $this->get('/shop');
    $response->assertSuccessful();
    $response->assertSee('Inicio');
    $response->assertSee('Tienda');
    $response->assertSee('Custom brand description');
    $response->assertSee('2026 Custom Copyright');
});

it('renders shop with hidden filters', function () {
    $this->settings->update([
        'shop_page_config' => [
            'products_per_page' => 24,
            'grid_columns' => 4,
            'show_brand_filter' => false,
            'show_category_filter' => false,
        ],
    ]);

    $response = $this->get('/shop');
    $response->assertSuccessful();
    $response->assertDontSee('lg:w-1/4', false);
});

it('renders shop with solid dark navbar', function () {
    $this->settings->update([
        'navbar_config' => ['show_search' => true, 'show_categories_button' => true, 'style' => 'solid_dark', 'menu_items' => []],
    ]);

    $response = $this->get('/shop');
    $response->assertSuccessful();
    $response->assertSee('bg-slate-900', false);
});

it('renders homepage with custom typography', function () {
    $this->settings->update([
        'typography_config' => [
            'body_font' => 'Inter',
            'heading_font' => 'Playfair Display',
            'font_size_scale' => 'large',
        ],
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('fonts.googleapis.com', false);
    $response->assertSee("font-family: 'Inter'", false);
    $response->assertSee('font-size: 18px', false);
});

it('renders homepage with custom CSS', function () {
    $this->settings->update([
        'custom_css' => '.hero-section { background: linear-gradient(red, blue); }',
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('.hero-section { background: linear-gradient(red, blue); }', false);
});

it('renders product page with reviews hidden', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'is_active' => true,
    ]);

    $this->settings->update([
        'product_page_config' => [
            'show_related_products' => false,
            'show_reviews' => false,
            'show_specifications' => true,
            'related_products_count' => 4,
        ],
    ]);

    $response = $this->get('/products/' . $product->slug);
    $response->assertSuccessful();
});

it('renders about page with default content', function () {
    $this->get('/about')->assertSuccessful();
});

it('renders about page with custom admin content', function () {
    $this->settings->update([
        'about_page_config' => [
            'title' => 'Nuestra Historia Custom',
            'description' => 'Descripcion personalizada de la tienda.',
            'cta_text' => 'Innovamos cada dia',
            'values' => [
                ['icon' => 'check-circle', 'title' => 'Calidad Total', 'description' => 'Solo lo mejor'],
            ],
        ],
    ]);

    $response = $this->get('/about');
    $response->assertSuccessful();
    $response->assertSee('Nuestra Historia Custom');
    $response->assertSee('Descripcion personalizada de la tienda.');
    $response->assertSee('Innovamos cada dia');
    $response->assertSee('Calidad Total');
});

it('renders store policy page when published', function () {
    $this->settings->update([
        'store_policies' => [
            [
                'title' => 'Politica de Devoluciones',
                'slug' => 'devoluciones',
                'is_active' => true,
                'content' => '<p>Aceptamos devoluciones dentro de 30 dias de la compra.</p>',
            ],
        ],
    ]);

    $response = $this->get('/politicas/devoluciones');
    $response->assertSuccessful();
    $response->assertSee('Politica de Devoluciones');
    $response->assertSee('Aceptamos devoluciones dentro de 30 dias de la compra.');
});

it('returns 404 for unpublished store policy', function () {
    $this->settings->update([
        'store_policies' => [
            [
                'title' => 'Draft Policy',
                'slug' => 'draft',
                'is_active' => false,
                'content' => '<p>Not published yet</p>',
            ],
        ],
    ]);

    $this->get('/politicas/draft')->assertNotFound();
});

it('returns 404 for nonexistent store policy', function () {
    $this->get('/politicas/nonexistent')->assertNotFound();
});
