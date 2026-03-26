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

// --- Hero Section ---

it('renders hero section with custom heading from config', function () {
    HomepageSection::factory()->hero()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => array_merge(SectionType::Hero->defaultConfig(), [
            'heading' => 'Bienvenido a Mi Tienda Custom',
        ]),
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Bienvenido a Mi Tienda Custom');
});

// --- CTA Strip ---

it('renders cta strip with heading and description', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::CtaStrip,
        'name' => 'CTA Test',
        'sort_order' => 1,
        'config' => [
            'heading' => 'Repuestos Especiales',
            'description' => 'Los mejores repuestos del mercado',
            'button_text' => 'Comprar Ahora',
            'button_url' => '/shop',
            'icon' => 'truck',
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Repuestos Especiales')
        ->assertSee('Los mejores repuestos del mercado')
        ->assertSee('Comprar Ahora');
});

// --- Promo Banners ---

it('renders promo banners with banner titles', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::PromoBanners,
        'name' => 'Banners',
        'sort_order' => 1,
        'config' => [
            'banners' => [
                [
                    'title' => 'Oferta Flash Especial',
                    'subtitle' => 'Solo por hoy',
                    'badge_text' => 'Hot',
                    'badge_color' => 'red',
                    'button_text' => 'Ver',
                    'button_url' => '#',
                    'image' => null,
                ],
            ],
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Oferta Flash Especial')
        ->assertSee('Solo por hoy');
});

// --- Value Props ---

it('renders value props with item titles', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::ValueProps,
        'name' => 'Props',
        'sort_order' => 1,
        'config' => [
            'items' => [
                ['icon' => 'truck', 'title' => 'Envio Express', 'description' => 'En 24 horas'],
                ['icon' => 'shield', 'title' => 'Pago Seguro', 'description' => 'Con encriptacion'],
            ],
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Envio Express')
        ->assertSee('Pago Seguro');
});

// --- Product Grid ---

it('product grid with source trending shows products ordered by views', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'name' => 'Producto Popular',
        'is_active' => true,
        'views_count' => 100,
    ]);

    HomepageSection::factory()->productGrid()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => ['heading' => 'Tendencias', 'source' => 'trending', 'limit' => 8, 'enable_infinite_scroll' => false],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Tendencias')
        ->assertSee('Producto Popular');
});

it('product grid with source featured shows only featured products', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'name' => 'Producto Destacado',
        'is_active' => true,
        'is_featured' => true,
    ]);

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'name' => 'Producto Normal',
        'is_active' => true,
        'is_featured' => false,
    ]);

    HomepageSection::factory()->productGrid()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => ['heading' => 'Destacados', 'source' => 'featured', 'limit' => 8, 'enable_infinite_scroll' => false],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Producto Destacado')
        ->assertDontSee('Producto Normal');
});

it('product grid with source new shows latest products', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'name' => 'Producto Reciente',
        'is_active' => true,
    ]);

    HomepageSection::factory()->productGrid()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => ['heading' => 'Nuevos', 'source' => 'new', 'limit' => 8, 'enable_infinite_scroll' => false],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Producto Reciente');
});

it('product grid with source category shows only products from that category', function () {
    $targetCategory = Category::factory()->create(['tenant_id' => $this->tenant->id]);
    $otherCategory = Category::factory()->create(['tenant_id' => $this->tenant->id]);

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $targetCategory->id,
        'name' => 'Producto En Categoria',
        'is_active' => true,
    ]);

    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $otherCategory->id,
        'name' => 'Producto Otra Categoria',
        'is_active' => true,
    ]);

    HomepageSection::factory()->productGrid()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => [
            'heading' => 'Por Categoria',
            'source' => 'category',
            'category_id' => $targetCategory->id,
            'limit' => 8,
            'enable_infinite_scroll' => false,
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Producto En Categoria')
        ->assertDontSee('Producto Otra Categoria');
});

it('product grid respects limit config', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

    $products = Product::factory()->count(5)->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'is_active' => true,
        'is_featured' => true,
    ]);

    HomepageSection::factory()->productGrid()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => ['heading' => 'Limited', 'source' => 'featured', 'limit' => 2, 'enable_infinite_scroll' => false],
    ]);

    $response = $this->get('/');
    $response->assertOk();

    // Count how many product names appear in the response
    $content = $response->getContent();
    $foundCount = 0;
    foreach ($products as $product) {
        if (str_contains($content, $product->name)) {
            $foundCount++;
        }
    }

    expect($foundCount)->toBeLessThanOrEqual(2);
});

it('product grid renders without errors when no products exist', function () {
    HomepageSection::factory()->productGrid()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'config' => ['heading' => 'Vacio', 'source' => 'trending', 'limit' => 8, 'enable_infinite_scroll' => false],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Vacio');
});

// --- Category Strip ---

it('category strip with empty config loads featured categories', function () {
    Category::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Categoria Destacada',
        'is_featured' => true,
        'is_active' => true,
    ]);

    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::CategoryStrip,
        'name' => 'Categorias',
        'sort_order' => 1,
        'config' => ['categories' => []],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Categoria Destacada');
});

it('category strip with specific categories loads those categories', function () {
    $cat1 = Category::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Laptops Custom',
        'is_active' => true,
    ]);

    Category::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Categoria No Incluida',
        'is_active' => true,
    ]);

    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::CategoryStrip,
        'name' => 'Categorias',
        'sort_order' => 1,
        'config' => [
            'categories' => [
                ['category_id' => $cat1->id, 'color_class' => 'bg-blue-100 text-blue-600'],
            ],
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Laptops Custom')
        ->assertDontSee('Categoria No Incluida');
});

it('category strip renders without errors when no categories exist', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::CategoryStrip,
        'name' => 'Categorias',
        'sort_order' => 1,
        'config' => ['categories' => []],
    ]);

    $this->get('/')->assertOk();
});

// --- Brand Slider ---

it('brand slider shows active brands', function () {
    Brand::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Marca Test Alpha',
        'is_active' => true,
    ]);

    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::BrandSlider,
        'name' => 'Marcas',
        'sort_order' => 1,
        'config' => ['heading' => 'Nuestras Marcas', 'subheading' => 'Las mejores'],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Nuestras Marcas')
        ->assertSee('Marca Test Alpha');
});

it('brand slider does not render section when no brands exist', function () {
    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::BrandSlider,
        'name' => 'Marcas',
        'sort_order' => 1,
        'config' => ['heading' => 'Marcas Vacias', 'subheading' => 'Sin marcas'],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Marcas Vacias');
});
