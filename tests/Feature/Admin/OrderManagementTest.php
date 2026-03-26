<?php

use App\Enums\Module;
use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
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

    $this->customer = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('can render orders list page', function () {
    $this->get(OrderResource::getUrl('index'))->assertSuccessful();
});

it('can list orders in table', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
    ]);

    Livewire::test(ListOrders::class)
        ->assertCanSeeTableRecords([$order]);
});

it('can render edit order page', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
    ]);

    $this->get(OrderResource::getUrl('edit', ['record' => $order]))
        ->assertSuccessful();
});

it('enforces tenant scoping on orders', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
    Order::factory()->create([
        'tenant_id' => $otherTenant->id,
        'user_id' => $otherUser->id,
    ]);

    expect(Order::count())->toBe(0);
});

it('order status can transition from pending to confirmed', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
        'status' => OrderStatus::PENDING,
    ]);

    expect($order->status->canTransitionTo(OrderStatus::CONFIRMED))->toBeTrue();

    $order->updateStatus(OrderStatus::CONFIRMED);
    expect($order->fresh()->status)->toBe(OrderStatus::CONFIRMED);
});

it('order status transitions follow valid flow', function () {
    expect(OrderStatus::PENDING->canTransitionTo(OrderStatus::CONFIRMED))->toBeTrue()
        ->and(OrderStatus::CONFIRMED->canTransitionTo(OrderStatus::PROCESSING))->toBeTrue()
        ->and(OrderStatus::PROCESSING->canTransitionTo(OrderStatus::SHIPPED))->toBeTrue()
        ->and(OrderStatus::SHIPPED->canTransitionTo(OrderStatus::DELIVERED))->toBeTrue()
        ->and(OrderStatus::DELIVERED->canTransitionTo(OrderStatus::REFUNDED))->toBeTrue();
});

it('order status rejects invalid transitions', function () {
    expect(OrderStatus::CANCELLED->canTransitionTo(OrderStatus::PENDING))->toBeFalse()
        ->and(OrderStatus::REFUNDED->canTransitionTo(OrderStatus::PENDING))->toBeFalse()
        ->and(OrderStatus::PENDING->canTransitionTo(OrderStatus::SHIPPED))->toBeFalse()
        ->and(OrderStatus::PENDING->canTransitionTo(OrderStatus::DELIVERED))->toBeFalse();
});

it('can cancel order', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
        'status' => OrderStatus::PENDING,
    ]);

    $order->cancel('Test cancellation reason');
    expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);
});
