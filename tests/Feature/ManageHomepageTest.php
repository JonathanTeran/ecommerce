<?php

use App\Enums\Module;
use App\Enums\SectionType;
use App\Filament\Pages\ManageHomepage;
use App\Models\HomepageSection;
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

it('can render manage homepage page', function () {
    Livewire::test(ManageHomepage::class)
        ->assertSuccessful();
});

it('loads existing sections on mount', function () {
    HomepageSection::factory()->hero()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'name' => 'Mi Hero',
        'config' => SectionType::Hero->defaultConfig(),
    ]);

    HomepageSection::factory()->create([
        'tenant_id' => $this->tenant->id,
        'type' => SectionType::ValueProps,
        'name' => 'Mi Propuesta',
        'sort_order' => 2,
        'config' => SectionType::ValueProps->defaultConfig(),
    ]);

    $component = Livewire::test(ManageHomepage::class);

    $component->assertSuccessful();

    $sections = $component->get('data.sections');
    expect($sections)->toHaveCount(2);

    $values = array_values($sections);
    expect($values[0]['type'])->toBe('hero');
    expect($values[0]['name'])->toBe('Mi Hero');
    expect($values[1]['type'])->toBe('value_props');
    expect($values[1]['name'])->toBe('Mi Propuesta');
});

it('saves sections to database', function () {
    Livewire::test(ManageHomepage::class)
        ->set('data.sections', [
            [
                'type' => 'hero',
                'name' => 'Hero Guardado',
                'is_active' => true,
                'config' => SectionType::Hero->defaultConfig(),
            ],
            [
                'type' => 'brand_slider',
                'name' => 'Marcas Guardadas',
                'is_active' => true,
                'config' => SectionType::BrandSlider->defaultConfig(),
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $sections = HomepageSection::ordered()->get();
    expect($sections)->toHaveCount(2);
    expect($sections[0]->name)->toBe('Hero Guardado');
    expect($sections[0]->sort_order)->toBe(1);
    expect($sections[1]->name)->toBe('Marcas Guardadas');
    expect($sections[1]->sort_order)->toBe(2);
});

it('deletes previous sections when saving', function () {
    HomepageSection::factory()->hero()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'name' => 'Old Hero',
    ]);

    expect(HomepageSection::count())->toBe(1);

    Livewire::test(ManageHomepage::class)
        ->set('data.sections', [
            [
                'type' => 'value_props',
                'name' => 'New Props',
                'is_active' => true,
                'config' => SectionType::ValueProps->defaultConfig(),
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $sections = HomepageSection::all();
    expect($sections)->toHaveCount(1);
    expect($sections->first()->name)->toBe('New Props');
    expect($sections->where('name', 'Old Hero'))->toBeEmpty();
});

it('assigns correct sort order based on position', function () {
    Livewire::test(ManageHomepage::class)
        ->set('data.sections', [
            [
                'type' => 'brand_slider',
                'name' => 'Primero',
                'is_active' => true,
                'config' => SectionType::BrandSlider->defaultConfig(),
            ],
            [
                'type' => 'hero',
                'name' => 'Segundo',
                'is_active' => true,
                'config' => SectionType::Hero->defaultConfig(),
            ],
            [
                'type' => 'value_props',
                'name' => 'Tercero',
                'is_active' => true,
                'config' => SectionType::ValueProps->defaultConfig(),
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $sections = HomepageSection::ordered()->get();
    expect($sections[0]->sort_order)->toBe(1)->and($sections[0]->name)->toBe('Primero');
    expect($sections[1]->sort_order)->toBe(2)->and($sections[1]->name)->toBe('Segundo');
    expect($sections[2]->sort_order)->toBe(3)->and($sections[2]->name)->toBe('Tercero');
});

it('canAccess returns true when tenant has storefront module', function () {
    expect(ManageHomepage::canAccess())->toBeTrue();
});

it('canAccess returns false without tenant context', function () {
    app()->forgetInstance('current_tenant');

    expect(ManageHomepage::canAccess())->toBeFalse();
});

it('canAccess returns true for super admin without tenant', function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');
    $this->actingAs($superAdmin);

    app()->forgetInstance('current_tenant');

    expect(ManageHomepage::canAccess())->toBeTrue();
});

it('saves sections with style config to database', function () {
    Livewire::test(ManageHomepage::class)
        ->set('data.sections', [
            [
                'type' => 'hero',
                'name' => 'Hero Styled',
                'is_active' => true,
                'config' => array_merge(SectionType::Hero->defaultConfig(), [
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#1e3a8a',
                        'text_color' => '#ffffff',
                        'padding_preset' => 'spacious',
                        'border_radius' => 'medium',
                        'border_top' => false,
                        'border_bottom' => true,
                        'border_color' => '#ff0000',
                        'font_family' => 'Poppins',
                        'bg_gradient_from' => null,
                        'bg_gradient_to' => null,
                        'bg_gradient_direction' => 'to-b',
                    ],
                ]),
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $section = HomepageSection::first();
    expect($section->config['style']['bg_color'])->toBe('#1e3a8a');
    expect($section->config['style']['font_family'])->toBe('Poppins');
    expect($section->config['style']['padding_preset'])->toBe('spacious');
});

it('loads sections with existing style config on mount', function () {
    HomepageSection::factory()->hero()->create([
        'tenant_id' => $this->tenant->id,
        'sort_order' => 1,
        'name' => 'Styled Hero',
        'config' => array_merge(SectionType::Hero->defaultConfig(), [
            'style' => array_merge(\App\Support\SectionStyleHelper::defaultStyle(), [
                'bg_color' => '#ff6600',
                'font_family' => 'Inter',
            ]),
        ]),
    ]);

    $component = Livewire::test(ManageHomepage::class);
    $sections = $component->get('data.sections');
    $values = array_values($sections);

    expect($values[0]['config']['style']['bg_color'])->toBe('#ff6600');
    expect($values[0]['config']['style']['font_family'])->toBe('Inter');
});
