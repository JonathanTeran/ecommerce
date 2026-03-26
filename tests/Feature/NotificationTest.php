<?php

use App\Enums\OrderStatus;
use App\Enums\QuotationStatus;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Quotation;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use App\Notifications\QuotationStatusChanged;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'test-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => ['products', 'categories', 'orders', 'quotations', 'storefront'],
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('creates order status changed notification', function () {
    Notification::fake();

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::CONFIRMED,
    ]);

    $this->user->notify(new OrderStatusChanged(
        $order,
        OrderStatus::PENDING,
        OrderStatus::CONFIRMED,
    ));

    Notification::assertSentTo($this->user, OrderStatusChanged::class);
});

it('creates quotation status changed notification', function () {
    Notification::fake();

    $quotation = Quotation::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'quotation_number' => 'QUO-TEST001',
        'customer_name' => $this->user->name,
        'customer_email' => $this->user->email,
        'status' => QuotationStatus::Approved,
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'valid_until' => now()->addDays(30),
    ]);

    $this->user->notify(new QuotationStatusChanged(
        $quotation,
        QuotationStatus::Pending,
        QuotationStatus::Approved,
    ));

    Notification::assertSentTo($this->user, QuotationStatusChanged::class);
});

it('returns unread count via API', function () {
    $this->user->notifications()->create([
        'id' => Str::uuid(),
        'type' => OrderStatusChanged::class,
        'data' => ['message' => 'Test notification'],
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/notifications/unread-count');

    $response->assertOk()
        ->assertJsonStructure(['count']);

    expect($response->json('count'))->toBe(1);
});

it('marks notification as read', function () {
    $notification = $this->user->notifications()->create([
        'id' => Str::uuid(),
        'type' => OrderStatusChanged::class,
        'data' => ['message' => 'Test notification'],
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/notifications/{$notification->id}/read");

    $response->assertOk()
        ->assertJson(['message' => 'Notificación marcada como leída.']);

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();
});

it('marks all notifications as read', function () {
    $this->user->notifications()->create([
        'id' => Str::uuid(),
        'type' => OrderStatusChanged::class,
        'data' => ['message' => 'Test 1'],
    ]);
    $this->user->notifications()->create([
        'id' => Str::uuid(),
        'type' => OrderStatusChanged::class,
        'data' => ['message' => 'Test 2'],
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/notifications/read-all');

    $response->assertOk()
        ->assertJson(['message' => 'Todas las notificaciones marcadas como leídas.']);

    expect($this->user->unreadNotifications()->count())->toBe(0);
});
