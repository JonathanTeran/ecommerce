<?php

use App\Enums\Module;
use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Filament\Resources\ProductReturnResource;
use App\Filament\Resources\ProductReturnResource\Pages\EditProductReturn;
use App\Filament\Resources\ProductReturnResource\Pages\ListProductReturns;
use App\Models\Order;
use App\Models\Plan;
use App\Models\ProductReturn;
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

it('can render returns list page', function () {
    $this->get(ProductReturnResource::getUrl('index'))->assertSuccessful();
});

it('can list returns in table', function () {
    $order = Order::factory()->create(['tenant_id' => $this->tenant->id, 'user_id' => $this->admin->id]);

    $return = ProductReturn::factory()->create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $order->id,
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(ListProductReturns::class)
        ->assertCanSeeTableRecords([$return]);
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherOrder = Order::factory()->create(['tenant_id' => $otherTenant->id, 'user_id' => $otherUser->id]);

    ProductReturn::factory()->create([
        'tenant_id' => $otherTenant->id,
        'order_id' => $otherOrder->id,
        'user_id' => $otherUser->id,
    ]);

    expect(ProductReturn::count())->toBe(0);
});

it('can edit return status', function () {
    $order = Order::factory()->create(['tenant_id' => $this->tenant->id, 'user_id' => $this->admin->id]);

    $return = ProductReturn::factory()->create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $order->id,
        'user_id' => $this->admin->id,
        'status' => ReturnStatus::Requested,
        'reason' => ReturnReason::Defective,
    ]);

    Livewire::test(EditProductReturn::class, ['record' => $return->getRouteKey()])
        ->fillForm([
            'status' => ReturnStatus::Approved->value,
            'admin_notes' => 'Devolucion aprobada por el administrador',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::Approved)
        ->and($return->admin_notes)->toBe('Devolucion aprobada por el administrador');
});
