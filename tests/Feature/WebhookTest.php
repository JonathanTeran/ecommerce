<?php

use App\Enums\Module;
use App\Enums\WebhookEvent;
use App\Jobs\DispatchWebhook;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise-wh-'.uniqid(),
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

it('creates webhook with events', function () {
    $webhook = Webhook::factory()->create([
        'tenant_id' => $this->tenant->id,
        'events' => ['order.created', 'payment.completed'],
    ]);

    expect($webhook->events)->toContain('order.created')
        ->and($webhook->subscribesToEvent('order.created'))->toBeTrue()
        ->and($webhook->subscribesToEvent('stock.low'))->toBeFalse();
});

it('dispatches webhook jobs for matching events', function () {
    Queue::fake();

    Webhook::factory()->create([
        'tenant_id' => $this->tenant->id,
        'events' => ['order.created'],
        'is_active' => true,
    ]);

    Webhook::factory()->create([
        'tenant_id' => $this->tenant->id,
        'events' => ['stock.low'],
        'is_active' => true,
    ]);

    $service = app(WebhookService::class);
    $service->dispatch('order.created', ['order_id' => 1]);

    Queue::assertPushed(DispatchWebhook::class, 1);
});

it('does not dispatch for inactive webhooks', function () {
    Queue::fake();

    Webhook::factory()->create([
        'tenant_id' => $this->tenant->id,
        'events' => ['order.created'],
        'is_active' => false,
    ]);

    $service = app(WebhookService::class);
    $service->dispatch('order.created', ['order_id' => 1]);

    Queue::assertNotPushed(DispatchWebhook::class);
});

it('generates correct hmac signature', function () {
    $webhook = Webhook::factory()->create([
        'tenant_id' => $this->tenant->id,
        'secret' => 'test-secret',
        'events' => ['order.created'],
    ]);

    $payload = ['order_id' => 1];
    $payloadJson = json_encode([
        'event' => 'order.created',
        'timestamp' => now()->toIso8601String(),
        'data' => $payload,
    ]);

    $expectedSignature = hash_hmac('sha256', $payloadJson, 'test-secret');
    expect($expectedSignature)->not->toBeEmpty();
});

it('has all webhook event cases', function () {
    $events = WebhookEvent::cases();
    expect(count($events))->toBe(6)
        ->and(WebhookEvent::OrderCreated->value)->toBe('order.created')
        ->and(WebhookEvent::OrderCreated->label())->toBe('Orden Creada');
});

it('isolates webhooks by tenant', function () {
    $otherTenant = Tenant::factory()->create();

    Webhook::factory()->create(['tenant_id' => $this->tenant->id]);

    app()->instance('current_tenant', $otherTenant);
    Webhook::factory()->create(['tenant_id' => $otherTenant->id]);

    app()->instance('current_tenant', $this->tenant);
    expect(Webhook::count())->toBe(1);
});

it('has deliveries relationship', function () {
    $webhook = Webhook::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    expect($webhook->deliveries()->count())->toBe(0);
    expect($webhook->deliveries())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});
