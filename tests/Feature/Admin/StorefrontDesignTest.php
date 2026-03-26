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
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

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
        'site_name' => 'Test Store',
        'tax_rate' => 15.00,
    ]);

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render storefront design page', function () {
    $this->get(ManageStorefrontDesign::getUrl())->assertSuccessful();
});

it('can save navbar config', function () {
    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.navbar_config', [
            'show_search' => false,
            'show_categories_button' => false,
            'style' => 'solid_white',
            'menu_items' => [
                ['label' => 'Inicio', 'url' => '/', 'open_in_new_tab' => false, 'is_visible' => true],
                ['label' => 'Catalogo', 'url' => '/shop', 'open_in_new_tab' => false, 'is_visible' => true],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->navbar_config['show_search'])->toBeFalse()
        ->and($settings->navbar_config['style'])->toBe('solid_white')
        ->and($settings->navbar_config['menu_items'])->toHaveCount(2);
});

it('can save footer config', function () {
    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.footer_config', [
            'brand_description' => 'Mi tienda premium',
            'show_newsletter' => false,
            'copyright_text' => '2026 Mi Tienda',
            'show_payment_icons' => true,
            'columns' => [
                ['title' => 'Tienda', 'links' => [['label' => 'Productos', 'url' => '/shop']]],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->footer_config['brand_description'])->toBe('Mi tienda premium')
        ->and($settings->footer_config['show_newsletter'])->toBeFalse()
        ->and($settings->footer_config['columns'])->toHaveCount(1);
});

it('can save social links', function () {
    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.social_links', [
            'facebook_url' => 'https://facebook.com/test',
            'instagram_url' => 'https://instagram.com/test',
            'whatsapp_number' => '+593999999999',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->social_links['facebook_url'])->toBe('https://facebook.com/test')
        ->and($settings->social_links['whatsapp_number'])->toBe('+593999999999');
});

it('can save typography config', function () {
    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.typography_config', [
            'body_font' => 'Inter',
            'heading_font' => 'Playfair Display',
            'font_size_scale' => 'large',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->typography_config['body_font'])->toBe('Inter')
        ->and($settings->typography_config['heading_font'])->toBe('Playfair Display')
        ->and($settings->typography_config['font_size_scale'])->toBe('large');
});

it('can save custom css', function () {
    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.custom_css', '.hero { background: red; }')
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->custom_css)->toBe('.hero { background: red; }');
});

it('can save product page config', function () {
    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.product_page_config', [
            'show_related_products' => false,
            'show_reviews' => false,
            'show_specifications' => true,
            'related_products_count' => 6,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->product_page_config['show_related_products'])->toBeFalse()
        ->and($settings->product_page_config['show_reviews'])->toBeFalse()
        ->and($settings->product_page_config['related_products_count'])->toBe(6);
});

it('can save shop page config', function () {
    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.shop_page_config', [
            'products_per_page' => 24,
            'grid_columns' => 4,
            'show_brand_filter' => false,
            'show_category_filter' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->shop_page_config['products_per_page'])->toBe(24)
        ->and($settings->shop_page_config['grid_columns'])->toBe(4)
        ->and($settings->shop_page_config['show_brand_filter'])->toBeFalse();
});

it('can save about page config', function () {
    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.about_page_config', [
            'title' => 'Nuestra Historia',
            'description' => 'Somos una empresa dedicada a la excelencia.',
            'cta_text' => 'Innovacion y Calidad',
            'values' => [
                ['icon' => 'check-circle', 'title' => 'Calidad', 'description' => 'Solo lo mejor'],
                ['icon' => 'truck', 'title' => 'Envio Rapido', 'description' => 'En 24 horas'],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->about_page_config['title'])->toBe('Nuestra Historia')
        ->and($settings->about_page_config['cta_text'])->toBe('Innovacion y Calidad')
        ->and($settings->about_page_config['values'])->toHaveCount(2);
});

it('model returns defaults when config is null', function () {
    $settings = GeneralSetting::first();

    $navConfig = $settings->getNavbarConfig();
    expect($navConfig['show_search'])->toBeTrue()
        ->and($navConfig['style'])->toBe('transparent_on_scroll');

    $footerConfig = $settings->getFooterConfig();
    expect($footerConfig['show_newsletter'])->toBeTrue();

    $typoConfig = $settings->getTypographyConfig();
    expect($typoConfig['font_size_scale'])->toBe('normal');

    $productConfig = $settings->getProductPageConfig();
    expect($productConfig['show_reviews'])->toBeTrue()
        ->and($productConfig['show_related_products'])->toBeTrue();

    $shopConfig = $settings->getShopPageConfig();
    expect($shopConfig['grid_columns'])->toBe(3)
        ->and($shopConfig['products_per_page'])->toBe(12);

    $aboutConfig = $settings->getAboutPageConfig();
    expect($aboutConfig['title'])->toBe('')
        ->and($aboutConfig['values'])->toBe([]);

    $policies = $settings->getStorePolicies();
    expect($policies)->toBe([]);

    $domainConfig = $settings->getDomainConfig();
    expect($domainConfig['custom_domain'])->toBe('');
});

it('can save store policies', function () {
    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.store_policies', [
            [
                'title' => 'Politica de Devoluciones',
                'slug' => 'politica-de-devoluciones',
                'is_active' => true,
                'content' => '<p>Aceptamos devoluciones dentro de 30 dias.</p>',
            ],
            [
                'title' => 'Politica de Envios',
                'slug' => 'politica-de-envios',
                'is_active' => true,
                'content' => '<p>Envio gratis en compras mayores a $50.</p>',
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->store_policies)->toHaveCount(2)
        ->and($settings->store_policies[0]['title'])->toBe('Politica de Devoluciones')
        ->and($settings->store_policies[1]['slug'])->toBe('politica-de-envios');
});

it('can save domain config', function () {
    Livewire::test(ManageStorefrontDesign::class)
        ->set('data.domain_config', [
            'custom_domain' => 'www.mitienda.com',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = GeneralSetting::first();
    expect($settings->domain_config['custom_domain'])->toBe('www.mitienda.com');
});

it('can find policy by slug', function () {
    $this->settings->update([
        'store_policies' => [
            ['title' => 'Devoluciones', 'slug' => 'devoluciones', 'is_active' => true, 'content' => '<p>Content</p>'],
            ['title' => 'Envios', 'slug' => 'envios', 'is_active' => false, 'content' => '<p>Draft</p>'],
        ],
    ]);

    $policy = $this->settings->getStorePolicyBySlug('devoluciones');
    expect($policy)->not->toBeNull()
        ->and($policy['title'])->toBe('Devoluciones');

    $missing = $this->settings->getStorePolicyBySlug('nonexistent');
    expect($missing)->toBeNull();
});
