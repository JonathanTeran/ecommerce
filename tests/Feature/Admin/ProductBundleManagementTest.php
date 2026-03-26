<?php

use App\Enums\Module;
use App\Filament\Resources\ProductBundleResource;
use App\Filament\Resources\ProductBundleResource\Pages\CreateProductBundle;
use App\Filament\Resources\ProductBundleResource\Pages\ListProductBundles;
use App\Models\Plan;
use App\Models\ProductBundle;
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

it('can render bundles list page', function () {
    $this->get(ProductBundleResource::getUrl('index'))->assertSuccessful();
});

it('can render create bundle page', function () {
    $this->get(ProductBundleResource::getUrl('create'))->assertSuccessful();
});

it('can list bundles in table', function () {
    $bundle = ProductBundle::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    Livewire::test(ListProductBundles::class)
        ->assertCanSeeTableRecords([$bundle]);
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    ProductBundle::factory()->create([
        'tenant_id' => $otherTenant->id,
    ]);

    expect(ProductBundle::count())->toBe(0);
});
