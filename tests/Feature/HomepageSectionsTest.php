<?php

use App\Enums\Module;
use App\Enums\SectionType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Plan;
use App\Models\Product;
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

it('renders homepage with dynamic sections from database', function () {
    HomepageSection::factory()->hero()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'is_active' => true,
        'config' => SectionType::Hero->defaultConfig(),
    ]);

    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::ValueProps,
        'name' => 'Propuesta de Valor',
        'sort_order' => 2,
        'is_active' => true,
        'config' => SectionType::ValueProps->defaultConfig(),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Potencia tu Vida Digital');
    $response->assertSee('Envío Gratis');
});

it('renders empty state when no sections exist', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Bienvenido a nuestra tienda');
});

it('does not render inactive sections', function () {
    HomepageSection::factory()->hero()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'is_active' => true,
        'config' => SectionType::Hero->defaultConfig(),
    ]);

    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::CtaStrip,
        'name' => 'CTA Inactivo',
        'sort_order' => 2,
        'is_active' => false,
        'config' => [
            'heading' => 'Seccion Oculta',
            'description' => 'No deberia aparecer',
            'button_text' => 'Click',
            'button_url' => '#',
            'icon' => 'puzzle',
        ],
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Potencia tu Vida Digital');
    $response->assertDontSee('Seccion Oculta');
});

it('respects section sort order', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::ValueProps,
        'name' => 'Props',
        'sort_order' => 2,
        'is_active' => true,
        'config' => SectionType::ValueProps->defaultConfig(),
    ]);

    HomepageSection::factory()->hero()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'is_active' => true,
        'config' => SectionType::Hero->defaultConfig(),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $heroPos = strpos($response->getContent(), 'Potencia tu Vida Digital');
    $propsPos = strpos($response->getContent(), 'Envío Gratis');

    expect($heroPos)->toBeLessThan($propsPos);
});

it('scopes homepage sections to current tenant', function () {
    $otherTenant = Tenant::factory()->create(['slug' => 'other-store']);

    HomepageSection::factory()->hero()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => SectionType::Hero->defaultConfig(),
    ]);

    HomepageSection::factory()->create([
        'tenant_id' => $otherTenant->id,
        'type' => SectionType::CtaStrip,
        'name' => 'Other Tenant CTA',
        'sort_order' => 1,
        'config' => [
            'heading' => 'Otro Tenant CTA',
            'description' => 'No deberia aparecer',
            'button_text' => 'Click',
            'button_url' => '#',
            'icon' => 'puzzle',
        ],
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Potencia tu Vida Digital');
    $response->assertDontSee('Otro Tenant CTA');
});

it('renders all 7 section types together on homepage', function () {
    $category = Category::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_featured' => true,
        'is_active' => true,
    ]);
    Brand::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
    ]);
    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $sectionTypes = SectionType::cases();
    foreach ($sectionTypes as $index => $type) {
        HomepageSection::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => $type,
            'name' => $type->label(),
            'sort_order' => $index + 1,
            'is_active' => true,
            'config' => $type->defaultConfig(),
        ]);
    }

    $response = $this->get('/');

    $response->assertOk();
    // Hero
    $response->assertSee('Potencia tu Vida Digital');
    // CTA
    $response->assertSee('Repuestos para tu Laptop');
    // Value Props
    $response->assertSee('Envío Gratis');
    // Promo banners
    $response->assertSee('Construye tu PC Ideal');
});

it('renders hero with custom config values instead of defaults', function () {
    HomepageSection::factory()->hero()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => [
            'badge_text' => 'Oferta Especial',
            'heading' => 'Titulo Personalizado',
            'subheading' => 'Subtitulo custom',
            'cta_text' => 'Comprar Ya',
            'cta_url' => '/ofertas',
            'secondary_cta_text' => null,
            'secondary_cta_url' => null,
            'background_image' => null,
        ],
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Titulo Personalizado');
    $response->assertSee('Oferta Especial');
    $response->assertSee('Comprar Ya');
    $response->assertDontSee('Potencia tu Vida Digital');
});

it('renders hero with custom background image path', function () {
    HomepageSection::factory()->hero()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => array_merge(SectionType::Hero->defaultConfig(), [
            'background_image' => 'homepage/custom-hero.jpg',
        ]),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('homepage/custom-hero.jpg');
});

it('renders cta strip with truck icon variant', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::CtaStrip,
        'name' => 'CTA Envio',
        'sort_order' => 1,
        'config' => [
            'heading' => 'Envio Gratis',
            'description' => 'En compras mayores',
            'button_text' => 'Comprar',
            'button_url' => '/shop',
            'icon' => 'truck',
        ],
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Envio Gratis');
});

it('renders multiple product grids with different sources', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'is_active' => true,
        'is_featured' => true,
        'views_count' => 50,
    ]);

    HomepageSection::factory()->productGrid()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => ['heading' => 'Los Mas Vistos', 'source' => 'trending', 'limit' => 4, 'enable_infinite_scroll' => false],
    ]);

    HomepageSection::factory()->productGrid()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 2,
        'name' => 'Destacados Grid',
        'config' => ['heading' => 'Productos Destacados', 'source' => 'featured', 'limit' => 4, 'enable_infinite_scroll' => false],
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Los Mas Vistos');
    $response->assertSee('Productos Destacados');
});

// --- Visual Style Customization ---

it('renders section with custom background color via inline style', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::ValueProps,
        'name' => 'Props Styled',
        'sort_order' => 1,
        'is_active' => true,
        'config' => array_merge(SectionType::ValueProps->defaultConfig(), [
            'style' => array_merge(\App\Support\SectionStyleHelper::defaultStyle(), [
                'bg_color' => '#1e3a8a',
            ]),
        ]),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('background-color: #1e3a8a', false);
});

it('renders section with gradient background via inline style', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::CtaStrip,
        'name' => 'CTA Gradient',
        'sort_order' => 1,
        'is_active' => true,
        'config' => [
            'heading' => 'Gradient CTA',
            'description' => 'Test gradient',
            'button_text' => 'Click',
            'button_url' => '#',
            'icon' => 'truck',
            'style' => array_merge(\App\Support\SectionStyleHelper::defaultStyle(), [
                'bg_type' => 'gradient',
                'bg_gradient_from' => '#000000',
                'bg_gradient_to' => '#ffffff',
                'bg_gradient_direction' => 'to-r',
            ]),
        ],
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('linear-gradient(to right, #000000, #ffffff)', false);
});

it('renders section with custom text color via inline style', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::ValueProps,
        'name' => 'Props Text Color',
        'sort_order' => 1,
        'is_active' => true,
        'config' => array_merge(SectionType::ValueProps->defaultConfig(), [
            'style' => array_merge(\App\Support\SectionStyleHelper::defaultStyle(), [
                'text_color' => '#ff6600',
            ]),
        ]),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('color: #ff6600', false);
});

it('renders section with custom font family via inline style', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::ValueProps,
        'name' => 'Props Font',
        'sort_order' => 1,
        'is_active' => true,
        'config' => array_merge(SectionType::ValueProps->defaultConfig(), [
            'style' => array_merge(\App\Support\SectionStyleHelper::defaultStyle(), [
                'font_family' => 'Poppins',
            ]),
        ]),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee("font-family: 'Poppins', sans-serif", false);
});

it('renders section without style key using default classes', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::ValueProps,
        'name' => 'Props Default',
        'sort_order' => 1,
        'is_active' => true,
        'config' => [
            'items' => [
                ['icon' => 'truck', 'title' => 'Envio Test', 'description' => 'Desc'],
            ],
        ],
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('bg-zinc-900', false);
    $response->assertSee('Envio Test');
});

it('loads google fonts link when sections use custom fonts', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::ValueProps,
        'name' => 'Props Fonts',
        'sort_order' => 1,
        'is_active' => true,
        'config' => array_merge(SectionType::ValueProps->defaultConfig(), [
            'style' => array_merge(\App\Support\SectionStyleHelper::defaultStyle(), [
                'font_family' => 'Montserrat',
            ]),
        ]),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('fonts.googleapis.com', false);
    $response->assertSee('Montserrat', false);
});
