<?php

use App\Enums\Module;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
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

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    $this->category = Category::create([
        'name' => 'Laptops', 'slug' => 'laptops', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $this->brand = Brand::create([
        'name' => 'Dell', 'slug' => 'dell', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
});

it('can render products list page', function () {
    $this->get(ProductResource::getUrl('index'))->assertSuccessful();
});

it('can render create product page', function () {
    $this->get(ProductResource::getUrl('create'))->assertSuccessful();
});

it('can create product', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Laptop Dell XPS',
            'slug' => 'laptop-dell-xps',
            'price' => 1200.00,
            'quantity' => 10,
            'sku' => 'DELL-XPS-001',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'is_active' => true,
            'low_stock_threshold' => 5,
            'variants' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::where('slug', 'laptop-dell-xps')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

it('can edit product', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
    ]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['price' => 999.99])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->fresh()->price)->toBe('999.99');
});

it('can toggle product active state', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'is_active' => true,
    ]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->fresh()->is_active)->toBeFalse();
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    $otherProduct = Product::factory()->create([
        'tenant_id' => $otherTenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
    ]);

    $visibleProducts = Product::all();
    expect($visibleProducts->contains($otherProduct))->toBeFalse();
});

it('can list products in table', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
    ]);

    Livewire::test(ListProducts::class)
        ->assertCanSeeTableRecords([$product]);
});

it('product inherits tenant_id on creation', function () {
    $product = Product::create([
        'name' => 'Auto Tenant',
        'slug' => 'auto-tenant',
        'price' => 50,
        'quantity' => 1,
        'sku' => 'AUTO-001',
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
    ]);

    expect($product->tenant_id)->toBe($this->tenant->id);
});

it('can render edit product page', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
    ]);

    $this->get(ProductResource::getUrl('edit', ['record' => $product]))
        ->assertSuccessful();
});

it('product factory creates valid product', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
    ]);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->name)->not->toBeEmpty()
        ->and($product->price)->not->toBeNull()
        ->and($product->tenant_id)->toBe($this->tenant->id);
});
