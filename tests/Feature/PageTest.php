<?php

use App\Enums\Module;
use App\Models\Page;
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

it('renders a published page at /pagina/{slug}', function () {
    Page::factory()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Quienes Somos',
        'slug' => 'quienes-somos',
        'is_published' => true,
        'published_at' => now(),
        'content' => [
            ['type' => 'rich_text', 'data' => ['content' => '<p>Somos una empresa dedicada a la tecnologia.</p>']],
        ],
    ]);

    $response = $this->get('/pagina/quienes-somos');

    $response->assertOk();
    $response->assertSee('Somos una empresa dedicada a la tecnologia.');
});

it('returns 404 for unpublished page', function () {
    Page::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Borrador',
        'slug' => 'borrador',
    ]);

    $response = $this->get('/pagina/borrador');

    $response->assertNotFound();
});

it('returns 404 for nonexistent slug', function () {
    $response = $this->get('/pagina/no-existe');

    $response->assertNotFound();
});

it('scopes pages to current tenant', function () {
    $otherTenant = Tenant::factory()->create(['slug' => 'other-store']);

    Page::factory()->create([
        'tenant_id' => $otherTenant->id,
        'title' => 'Pagina de Otro Tenant',
        'slug' => 'otra-pagina',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $response = $this->get('/pagina/otra-pagina');

    $response->assertNotFound();
});

it('renders rich text block content', function () {
    Page::factory()->withRichTextBlock()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Test Rich Text',
        'slug' => 'test-rich-text',
    ]);

    $response = $this->get('/pagina/test-rich-text');

    $response->assertOk();
    $response->assertSee('Contenido de ejemplo para la pagina.');
});

it('renders hero banner block', function () {
    Page::factory()->withHeroBlock()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Test Hero',
        'slug' => 'test-hero',
    ]);

    $response = $this->get('/pagina/test-hero');

    $response->assertOk();
    $response->assertSee('Bienvenidos');
    $response->assertSee('Descubre nuestros servicios');
});

it('renders multiple blocks in order', function () {
    Page::factory()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Multi Blocks',
        'slug' => 'multi-blocks',
        'is_published' => true,
        'published_at' => now(),
        'content' => [
            ['type' => 'rich_text', 'data' => ['content' => '<p>Primer bloque de texto.</p>']],
            ['type' => 'call_to_action', 'data' => ['heading' => 'Segundo bloque CTA', 'description' => 'Descripcion CTA', 'button_text' => 'Click Aqui', 'button_url' => '/shop']],
        ],
    ]);

    $response = $this->get('/pagina/multi-blocks');

    $response->assertOk();
    $firstPos = strpos($response->getContent(), 'Primer bloque de texto.');
    $secondPos = strpos($response->getContent(), 'Segundo bloque CTA');

    expect($firstPos)->toBeLessThan($secondPos);
});

it('renders page with SEO meta tags', function () {
    Page::factory()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'SEO Page',
        'slug' => 'seo-page',
        'is_published' => true,
        'published_at' => now(),
        'meta_title' => 'Mi Pagina SEO Personalizada',
        'meta_description' => 'Descripcion SEO para buscadores',
        'content' => [
            ['type' => 'rich_text', 'data' => ['content' => '<p>Contenido SEO.</p>']],
        ],
    ]);

    $response = $this->get('/pagina/seo-page');

    $response->assertOk();
    $response->assertSee('Mi Pagina SEO Personalizada', false);
});

it('renders faq block with accordion', function () {
    Page::factory()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'FAQ Page',
        'slug' => 'faq-page',
        'is_published' => true,
        'published_at' => now(),
        'content' => [
            [
                'type' => 'faq',
                'data' => [
                    'items' => [
                        ['question' => 'Como puedo comprar?', 'answer' => 'Agrega productos al carrito y realiza el checkout.'],
                        ['question' => 'Cuanto tarda el envio?', 'answer' => 'Entre 2 y 5 dias habiles.'],
                    ],
                ],
            ],
        ],
    ]);

    $response = $this->get('/pagina/faq-page');

    $response->assertOk();
    $response->assertSee('Como puedo comprar?');
    $response->assertSee('Cuanto tarda el envio?');
});

it('renders features grid block', function () {
    Page::factory()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Servicios',
        'slug' => 'servicios',
        'is_published' => true,
        'published_at' => now(),
        'content' => [
            [
                'type' => 'features_grid',
                'data' => [
                    'columns' => 3,
                    'features' => [
                        ['icon' => 'truck', 'title' => 'Envio Rapido', 'description' => 'Entrega en 24 horas'],
                        ['icon' => 'shield', 'title' => 'Garantia Total', 'description' => 'Cobertura completa'],
                    ],
                ],
            ],
        ],
    ]);

    $response = $this->get('/pagina/servicios');

    $response->assertOk();
    $response->assertSee('Envio Rapido');
    $response->assertSee('Garantia Total');
});

it('does not show future-scheduled published pages', function () {
    Page::factory()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Pagina Futura',
        'slug' => 'pagina-futura',
        'is_published' => true,
        'published_at' => now()->addDays(7),
    ]);

    $response = $this->get('/pagina/pagina-futura');

    $response->assertNotFound();
});

it('renders empty state when page has no content blocks', function () {
    Page::factory()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Pagina Vacia',
        'slug' => 'pagina-vacia',
        'is_published' => true,
        'published_at' => now(),
        'content' => [],
    ]);

    $response = $this->get('/pagina/pagina-vacia');

    $response->assertOk();
    $response->assertSee('Pagina Vacia');
    $response->assertSee('Esta pagina aun no tiene contenido.');
});
