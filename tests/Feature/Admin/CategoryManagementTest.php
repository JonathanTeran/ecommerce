<?php

use App\Enums\Module;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
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

it('can render categories list page', function () {
    $this->get(CategoryResource::getUrl('index'))->assertSuccessful();
});

it('can create category', function () {
    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Computadoras',
            'slug' => 'computadoras',
            'is_active' => true,
            'is_featured' => false,
            'position' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::where('slug', 'computadoras')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

it('can create subcategory with parent', function () {
    $parent = Category::create([
        'name' => 'Electronics', 'slug' => 'electronics',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);

    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Laptops',
            'slug' => 'laptops',
            'parent_id' => $parent->id,
            'is_active' => true,
            'is_featured' => false,
            'position' => 0,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $child = Category::where('slug', 'laptops')->first();
    expect($child->parent_id)->toBe($parent->id);
});

it('can edit category', function () {
    $category = Category::create([
        'name' => 'Old Name', 'slug' => 'old-name',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => 'New Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->fresh()->name)->toBe('New Name');
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    Category::create([
        'name' => 'Other', 'slug' => 'other',
        'tenant_id' => $otherTenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);

    expect(Category::count())->toBe(0);
});

it('can toggle active and featured', function () {
    $category = Category::create([
        'name' => 'Toggle Test', 'slug' => 'toggle-test',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['is_active' => false, 'is_featured' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    $category->refresh();
    expect($category->is_active)->toBeFalse()
        ->and($category->is_featured)->toBeTrue();
});

it('can list categories in table', function () {
    $category = Category::create([
        'name' => 'Listed', 'slug' => 'listed',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);

    Livewire::test(ListCategories::class)
        ->assertCanSeeTableRecords([$category]);
});
