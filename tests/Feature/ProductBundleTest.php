<?php

use App\Enums\Module;
use App\Filament\Resources\ProductBundleResource;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

    $this->tenant = Tenant::factory()->create(['slug' => 'bundle-test-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise-bun-'.uniqid(),
        'type' => 'enterprise', 'price' => 150,
        'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);
});

function createTestProduct(string $name, float $price): Product
{
    $category = Category::firstOrCreate(['slug' => 'bun-test-cat'], ['name' => 'Test Category']);

    return Product::create([
        'name' => $name,
        'slug' => 'bun-'.uniqid(),
        'sku' => 'BUN-'.strtoupper(uniqid()),
        'price' => $price,
        'quantity' => 50,
        'category_id' => $category->id,
    ]);
}

it('creates a product bundle', function () {
    $bundle = ProductBundle::create([
        'name' => 'Kit de Oficina',
        'description' => 'Todo lo que necesitas',
        'price' => 150.00,
        'compare_price' => 200.00,
        'is_active' => true,
        'tenant_id' => $this->tenant->id,
    ]);

    expect($bundle)->toBeInstanceOf(ProductBundle::class)
        ->and($bundle->slug)->toBe('kit-de-oficina')
        ->and($bundle->is_active)->toBeTrue();
});

it('calculates discount percentage', function () {
    $bundle = ProductBundle::create([
        'name' => 'Discount Bundle',
        'price' => 75.00,
        'compare_price' => 100.00,
        'is_active' => true,
        'tenant_id' => $this->tenant->id,
    ]);

    expect($bundle->discount_percentage)->toBe(25);
});

it('returns null discount when no compare price', function () {
    $bundle = ProductBundle::create([
        'name' => 'No Discount',
        'price' => 75.00,
        'is_active' => true,
        'tenant_id' => $this->tenant->id,
    ]);

    expect($bundle->discount_percentage)->toBeNull();
});

it('has many bundle items with products', function () {
    $bundle = ProductBundle::create([
        'name' => 'Multi Item Bundle',
        'price' => 250.00,
        'is_active' => true,
        'tenant_id' => $this->tenant->id,
    ]);

    $product1 = createTestProduct('Laptop', 800);
    $product2 = createTestProduct('Mouse', 30);
    $product3 = createTestProduct('Keyboard', 50);

    $bundle->items()->createMany([
        ['product_id' => $product1->id, 'quantity' => 1],
        ['product_id' => $product2->id, 'quantity' => 1],
        ['product_id' => $product3->id, 'quantity' => 1],
    ]);

    expect($bundle->items)->toHaveCount(3)
        ->and($bundle->items->first()->product->name)->toBe('Laptop');
});

it('scopes active bundles', function () {
    ProductBundle::create([
        'name' => 'Active', 'price' => 100,
        'is_active' => true, 'tenant_id' => $this->tenant->id,
    ]);
    ProductBundle::create([
        'name' => 'Inactive', 'price' => 100,
        'is_active' => false, 'tenant_id' => $this->tenant->id,
    ]);

    expect(ProductBundle::active()->count())->toBe(1);
});

it('scopes available bundles by date', function () {
    ProductBundle::create([
        'name' => 'Available Now', 'price' => 100,
        'is_active' => true, 'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'tenant_id' => $this->tenant->id,
    ]);
    ProductBundle::create([
        'name' => 'Expired', 'price' => 100,
        'is_active' => true, 'starts_at' => now()->subWeek(),
        'ends_at' => now()->subDay(),
        'tenant_id' => $this->tenant->id,
    ]);
    ProductBundle::create([
        'name' => 'Future', 'price' => 100,
        'is_active' => true, 'starts_at' => now()->addDay(),
        'tenant_id' => $this->tenant->id,
    ]);

    expect(ProductBundle::available()->count())->toBe(1);
});

it('formats price correctly', function () {
    $bundle = ProductBundle::create([
        'name' => 'Formatted', 'price' => 1234.56,
        'is_active' => true, 'tenant_id' => $this->tenant->id,
    ]);

    expect($bundle->formatted_price)->toBe('$1,234.56');
});

it('resource is accessible with Bundles module', function () {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    expect(ProductBundleResource::canAccess())->toBeTrue();
});

it('soft deletes bundle', function () {
    $bundle = ProductBundle::create([
        'name' => 'To Delete', 'price' => 100,
        'is_active' => true, 'tenant_id' => $this->tenant->id,
    ]);

    $bundle->delete();

    expect(ProductBundle::count())->toBe(0)
        ->and(ProductBundle::withTrashed()->count())->toBe(1);
});
