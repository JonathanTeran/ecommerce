<?php

use App\Enums\Module;
use App\Filament\Resources\CouponResource;
use App\Filament\Resources\CouponResource\Pages\CreateCoupon;
use App\Filament\Resources\CouponResource\Pages\EditCoupon;
use App\Filament\Resources\CouponResource\Pages\ListCoupons;
use App\Models\Category;
use App\Models\Coupon;
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
});

it('can render coupons list page', function () {
    $this->get(CouponResource::getUrl('index'))->assertSuccessful();
});

it('can render create coupon page', function () {
    $this->get(CouponResource::getUrl('create'))->assertSuccessful();
});

it('can create a coupon', function () {
    Livewire::test(CreateCoupon::class)
        ->fillForm([
            'code' => 'SAVE20',
            'name' => 'Save 20%',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
            'usage_count' => 0,
            'first_order_only' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Coupon::where('code', 'SAVE20')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

it('can edit a coupon', function () {
    $coupon = Coupon::factory()->create(['tenant_id' => $this->tenant->id]);

    Livewire::test(EditCoupon::class, ['record' => $coupon->getRouteKey()])
        ->fillForm(['value' => 25.00])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($coupon->fresh()->value)->toBe('25.00');
});

it('can list coupons in table', function () {
    $coupon = Coupon::factory()->create(['tenant_id' => $this->tenant->id]);

    Livewire::test(ListCoupons::class)
        ->assertCanSeeTableRecords([$coupon]);
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    $otherCoupon = Coupon::factory()->create(['tenant_id' => $otherTenant->id]);

    $visibleCoupons = Coupon::all();
    expect($visibleCoupons->contains($otherCoupon))->toBeFalse();
});

it('can create fixed amount coupon', function () {
    Livewire::test(CreateCoupon::class)
        ->fillForm([
            'code' => 'FLAT5',
            'name' => 'Flat $5 off',
            'type' => 'fixed',
            'value' => 5.00,
            'min_order_amount' => 50.00,
            'is_active' => true,
            'usage_count' => 0,
            'first_order_only' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $coupon = Coupon::where('code', 'FLAT5')->first();
    expect($coupon->type)->toBe('fixed')
        ->and($coupon->min_order_amount)->toBe('50.00');
});

it('can create coupon with usage limits', function () {
    Livewire::test(CreateCoupon::class)
        ->fillForm([
            'code' => 'LIMITED',
            'name' => 'Limited Use',
            'type' => 'percentage',
            'value' => 10,
            'usage_limit' => 100,
            'usage_limit_per_user' => 1,
            'is_active' => true,
            'usage_count' => 0,
            'first_order_only' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $coupon = Coupon::where('code', 'LIMITED')->first();
    expect($coupon->usage_limit)->toBe(100)
        ->and($coupon->usage_limit_per_user)->toBe(1);
});

it('can toggle coupon active state', function () {
    $coupon = Coupon::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
    ]);

    Livewire::test(EditCoupon::class, ['record' => $coupon->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($coupon->fresh()->is_active)->toBeFalse();
});

it('coupon inherits tenant_id on creation', function () {
    $coupon = Coupon::create([
        'code' => 'AUTO',
        'name' => 'Auto Tenant',
        'type' => 'percentage',
        'value' => 5,
        'is_active' => true,
        'usage_count' => 0,
        'first_order_only' => false,
    ]);

    expect($coupon->tenant_id)->toBe($this->tenant->id);
});
