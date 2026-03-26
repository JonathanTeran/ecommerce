<?php

use App\Enums\Module;
use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use App\Filament\Resources\PaymentResource\Pages\ViewPayment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

function createPayment(array $attributes = []): Payment
{
    return Payment::create(array_merge([
        'transaction_id' => 'TXN-' . fake()->unique()->numerify('######'),
        'gateway' => 'payphone',
        'method' => 'credit_card',
        'amount' => 99.99,
        'currency' => 'USD',
        'status' => PaymentStatus::COMPLETED,
        'paid_at' => now(),
    ], $attributes));
}

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

it('can render the payments list', function () {
    Livewire::test(ListPayments::class)
        ->assertSuccessful();
});

it('can view a payment detail', function () {
    $order = Order::factory()->create(['tenant_id' => $this->tenant->id, 'user_id' => $this->admin->id]);
    $payment = createPayment([
        'tenant_id' => $this->tenant->id,
        'order_id' => $order->id,
    ]);

    Livewire::test(ViewPayment::class, ['record' => $payment->getRouteKey()])
        ->assertSuccessful();
});

it('has no create or edit pages', function () {
    $pages = PaymentResource::getPages();

    expect($pages)->toHaveKeys(['index', 'view']);
    expect($pages)->not->toHaveKey('create');
    expect($pages)->not->toHaveKey('edit');
});

it('has correct navigation group', function () {
    expect(PaymentResource::getNavigationGroup())->toBe('Gestión de Tienda');
});

it('requires orders module for access', function () {
    expect(PaymentResource::canAccess())->toBeTrue();

    app()->forgetInstance('current_tenant');

    expect(PaymentResource::canAccess())->toBeFalse();
});

it('shows payments in list', function () {
    $order = Order::factory()->create(['tenant_id' => $this->tenant->id, 'user_id' => $this->admin->id]);
    $payment = createPayment([
        'tenant_id' => $this->tenant->id,
        'order_id' => $order->id,
        'gateway' => 'stripe',
        'amount' => 150.00,
    ]);

    Livewire::test(ListPayments::class)
        ->assertCanSeeTableRecords([$payment]);
});
