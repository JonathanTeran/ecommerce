<?php

use App\Enums\Module;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\PageResource\Pages\ListPages;
use App\Models\Page;
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

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render the page list', function () {
    Livewire::test(ListPages::class)
        ->assertSuccessful();
});

it('can render the create page form', function () {
    Livewire::test(CreatePage::class)
        ->assertSuccessful();
});

it('can create a page', function () {
    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Nuestros Servicios',
            'slug' => 'nuestros-servicios',
            'is_published' => true,
            'sort_order' => 1,
            'content' => [
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<p>Ofrecemos los mejores servicios.</p>',
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Page::where('slug', 'nuestros-servicios')->exists())->toBeTrue();
});

it('can edit a page', function () {
    $page = Page::factory()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Pagina Original',
        'slug' => 'pagina-original',
    ]);

    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertSuccessful()
        ->assertFormSet([
            'title' => 'Pagina Original',
            'slug' => 'pagina-original',
        ]);
});

it('requires storefront module for access', function () {
    expect(PageResource::canAccess())->toBeTrue();

    app()->forgetInstance('current_tenant');

    expect(PageResource::canAccess())->toBeFalse();
});

it('has correct navigation group', function () {
    expect(PageResource::getNavigationGroup())->toBe('Apariencia');
});

it('saves content blocks to database', function () {
    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Pagina con Bloques',
            'slug' => 'pagina-con-bloques',
            'is_published' => true,
            'sort_order' => 0,
            'content' => [
                [
                    'type' => 'hero_banner',
                    'data' => [
                        'heading' => 'Titulo Hero',
                        'subheading' => 'Subtitulo Hero',
                        'cta_text' => 'Ver Mas',
                        'cta_url' => '/shop',
                    ],
                ],
                [
                    'type' => 'faq',
                    'data' => [
                        'items' => [
                            ['question' => 'Pregunta 1', 'answer' => 'Respuesta 1'],
                        ],
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::where('slug', 'pagina-con-bloques')->first();
    expect($page)->not->toBeNull();
    expect($page->content)->toHaveCount(2);
    expect($page->content[0]['type'])->toBe('hero_banner');
    expect($page->content[1]['type'])->toBe('faq');
});
