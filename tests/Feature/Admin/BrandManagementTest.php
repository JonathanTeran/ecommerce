<?php

use App\Enums\Module;
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\BrandResource\Pages\CreateBrand;
use App\Filament\Resources\BrandResource\Pages\EditBrand;
use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Models\Brand;
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

it('can render brands list page', function () {
    $this->get(BrandResource::getUrl('index'))->assertSuccessful();
});

it('can create brand', function () {
    Livewire::test(CreateBrand::class)
        ->fillForm([
            'name' => 'Apple',
            'slug' => 'apple',
            'is_active' => true,
            'is_featured' => false,
            'position' => 0,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('brands', [
        'name' => 'Apple',
        'tenant_id' => $this->tenant->id,
    ]);
});

it('can edit brand', function () {
    $brand = Brand::create([
        'name' => 'Old Brand', 'slug' => 'old-brand',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);

    Livewire::test(EditBrand::class, ['record' => $brand->getRouteKey()])
        ->fillForm(['name' => 'Updated Brand', 'website' => 'https://example.com'])
        ->call('save')
        ->assertHasNoFormErrors();

    $brand->refresh();
    expect($brand->name)->toBe('Updated Brand')
        ->and($brand->website)->toBe('https://example.com');
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    Brand::create([
        'name' => 'Hidden', 'slug' => 'hidden',
        'tenant_id' => $otherTenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);

    expect(Brand::count())->toBe(0);
});

it('can toggle active and featured', function () {
    $brand = Brand::create([
        'name' => 'Toggle', 'slug' => 'toggle',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);

    Livewire::test(EditBrand::class, ['record' => $brand->getRouteKey()])
        ->fillForm(['is_active' => false, 'is_featured' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    $brand->refresh();
    expect($brand->is_active)->toBeFalse()
        ->and($brand->is_featured)->toBeTrue();
});

it('can list brands in table', function () {
    $brand = Brand::create([
        'name' => 'Listed Brand', 'slug' => 'listed-brand',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);

    Livewire::test(ListBrands::class)
        ->assertCanSeeTableRecords([$brand]);
});
