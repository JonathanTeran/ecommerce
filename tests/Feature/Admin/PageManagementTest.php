<?php

use App\Enums\Module;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageResource\Pages\CreatePage;
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

it('can render pages list', function () {
    $this->get(PageResource::getUrl('index'))->assertSuccessful();
});

it('can create page', function () {
    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Sobre Nosotros',
            'slug' => 'sobre-nosotros',
            'is_published' => true,
            'sort_order' => 1,
            'meta_title' => 'Sobre Nosotros - Tienda',
            'meta_description' => 'Conoce mas sobre nuestra tienda.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Page::where('slug', 'sobre-nosotros')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

it('auto generates slug from title', function () {
    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Politica de Privacidad',
        ])
        ->assertFormSet([
            'slug' => 'politica-de-privacidad',
        ]);
});

it('can list pages in table', function () {
    $page = Page::factory()->create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Preguntas Frecuentes',
        'slug' => 'preguntas-frecuentes',
    ]);

    Livewire::test(ListPages::class)
        ->assertCanSeeTableRecords([$page]);
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    Page::factory()->create([
        'tenant_id' => $otherTenant->id,
        'title' => 'Pagina Oculta',
        'slug' => 'pagina-oculta',
    ]);

    expect(Page::count())->toBe(0);
});
