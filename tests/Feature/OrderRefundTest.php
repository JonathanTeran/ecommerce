<?php

use App\Enums\Module;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
use App\Models\Payment;
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

it('can process a full refund on a completed order', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->admin->id,
        'payment_status' => PaymentStatus::COMPLETED,
        'total' => 150.00,
    ]);

    Livewire::test(ListOrders::class)
        ->callTableAction('refund', $order, [
            'refund_type' => 'full',
            'refund_reason' => 'Cliente solicita reembolso total',
        ]);

    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::REFUNDED);
    expect(Payment::where('order_id', $order->id)->where('method', 'refund')->exists())->toBeTrue();
});

it('can process a partial refund', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->admin->id,
        'payment_status' => PaymentStatus::COMPLETED,
        'total' => 200.00,
    ]);

    Livewire::test(ListOrders::class)
        ->callTableAction('refund', $order, [
            'refund_type' => 'partial',
            'refund_amount' => 50.00,
            'refund_reason' => 'Producto dañado',
        ]);

    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::PARTIALLY_REFUNDED);

    $refund = Payment::where('order_id', $order->id)->where('method', 'refund')->first();
    expect($refund)->not->toBeNull();
    expect((float) $refund->refunded_amount)->toBe(50.00);
});

it('does not show refund action for unpaid orders', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->admin->id,
        'payment_status' => PaymentStatus::PENDING,
    ]);

    Livewire::test(ListOrders::class)
        ->assertTableActionHidden('refund', $order);
});
