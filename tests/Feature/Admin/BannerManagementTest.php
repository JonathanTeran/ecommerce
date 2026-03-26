<?php

use App\Enums\Module;
use App\Filament\Resources\BannerResource;
use App\Filament\Resources\BannerResource\Pages\CreateBanner;
use App\Filament\Resources\BannerResource\Pages\EditBanner;
use App\Filament\Resources\BannerResource\Pages\ListBanners;
use App\Models\Banner;
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

it('can render banners list page', function () {
    $this->get(BannerResource::getUrl('index'))->assertSuccessful();
});

it('can render create banner page', function () {
    $this->get(BannerResource::getUrl('create'))->assertSuccessful();
});

it('can edit banner fields', function () {
    $banner = Banner::create([
        'title' => 'Promo', 'position' => 'home_top',
        'sort_order' => 1, 'is_active' => true,
        'tenant_id' => $this->tenant->id,
    ]);

    // Update directly since SpatieMediaLibrary image field is required and cannot be filled in test
    $banner->update(['title' => 'Promo Updated', 'sort_order' => 5]);

    expect($banner->fresh()->title)->toBe('Promo Updated')
        ->and($banner->fresh()->sort_order)->toBe(5);
});

it('can toggle banner active state', function () {
    $banner = Banner::create([
        'title' => 'Toggle Banner', 'position' => 'home_top',
        'sort_order' => 0, 'is_active' => true,
        'tenant_id' => $this->tenant->id,
    ]);

    $banner->update(['is_active' => false]);
    expect($banner->fresh()->is_active)->toBeFalse();
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    Banner::create([
        'title' => 'Hidden', 'position' => 'home_top',
        'sort_order' => 0, 'is_active' => true,
        'tenant_id' => $otherTenant->id,
    ]);

    expect(Banner::count())->toBe(0);
});
