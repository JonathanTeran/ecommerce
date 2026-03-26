<?php

use App\Enums\Module;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'test-store']);
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

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'status' => OrderStatus::PENDING,
        'payment_status' => PaymentStatus::PENDING,
    ]);
});

it('shipping label denies unauthenticated access', function () {
    $response = $this->get("/order/{$this->order->id}/shipping-label");
    expect($response->status())->not->toBe(200);
});

it('invoice denies unauthenticated access', function () {
    $response = $this->get("/order/{$this->order->id}/invoice");
    expect($response->status())->not->toBe(200);
});

it('shipping label requires ownership', function () {
    $this->actingAs($this->otherUser);

    $this->get("/order/{$this->order->id}/shipping-label")
        ->assertForbidden();
});

it('invoice requires ownership', function () {
    $this->actingAs($this->otherUser);

    $this->get("/order/{$this->order->id}/invoice")
        ->assertForbidden();
});

it('owner can access their own shipping label', function () {
    $this->actingAs($this->user);

    $this->get("/order/{$this->order->id}/shipping-label")
        ->assertSuccessful();
});

it('owner can access their own invoice', function () {
    $this->actingAs($this->user);

    $this->get("/order/{$this->order->id}/invoice")
        ->assertSuccessful();
});

it('nuvei success denies unauthenticated access', function () {
    $response = $this->get("/nuvei/success/{$this->order->id}");
    expect($response->status())->not->toBe(200);
});

it('nuvei cancel denies unauthenticated access', function () {
    $response = $this->get("/nuvei/cancel/{$this->order->id}");
    expect($response->status())->not->toBe(200);
});

it('nuvei success requires ownership', function () {
    $this->actingAs($this->otherUser);

    $this->get("/nuvei/success/{$this->order->id}")
        ->assertForbidden();
});

it('nuvei cancel requires ownership', function () {
    $this->actingAs($this->otherUser);

    $this->get("/nuvei/cancel/{$this->order->id}")
        ->assertForbidden();
});

it('admin can access shipping label for any order in their tenant', function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $this->get("/order/{$this->order->id}/shipping-label")
        ->assertSuccessful();
});

it('admin can access invoice for any order in their tenant', function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $this->get("/order/{$this->order->id}/invoice")
        ->assertSuccessful();
});

it('sri xml download requires ownership or admin', function () {
    $this->actingAs($this->otherUser);

    $this->get("/admin/orders/{$this->order->id}/sri-xml")
        ->assertForbidden();
});

it('sri ride download requires ownership or admin', function () {
    $this->actingAs($this->otherUser);

    $this->get("/admin/orders/{$this->order->id}/ride")
        ->assertForbidden();
});
