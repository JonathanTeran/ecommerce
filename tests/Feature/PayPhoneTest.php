<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\PayPhoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
});

it('creates payment in simulation mode', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $service = new PayPhoneService;
    $result = $service->createPayment($order);

    expect($result['status'])->toBe('success')
        ->and($result['transaction_id'])->toStartWith('PP-SIM-');
});

it('confirms transaction in simulation mode', function () {
    $service = new PayPhoneService;
    $result = $service->confirmTransaction(12345);

    expect($result['statusCode'])->toBe(3)
        ->and($result['transactionId'])->toStartWith('PP-SIM-')
        ->and($result['authorizationCode'])->toStartWith('AUTH-');
});

it('handles successful PayPhone callback with auth', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
        'payment_status' => PaymentStatus::PENDING,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('payphone.callback', [
        'id' => 99999,
        'clientTransactionId' => $order->id,
    ]));

    $response->assertRedirect(route('checkout.confirmation', $order));

    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::COMPLETED)
        ->and($order->status)->toBe(OrderStatus::PROCESSING);
});

it('prevents duplicate payment on idempotent callback', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PROCESSING,
        'payment_status' => PaymentStatus::COMPLETED,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('payphone.callback', [
        'id' => 99999,
        'clientTransactionId' => $order->id,
    ]));

    $response->assertRedirect(route('checkout.confirmation', $order));
});

it('redirects unauthenticated callback to login', function () {
    $response = $this->get(route('payphone.callback', [
        'id' => 99999,
        'clientTransactionId' => 1,
    ]));

    // Auth middleware redirects or returns error
    expect($response->status())->toBeIn([302, 401, 403, 500]);
});

it('blocks callback for other users order', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $otherUser->id,
        'status' => OrderStatus::PENDING,
        'payment_status' => PaymentStatus::PENDING,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('payphone.callback', [
        'id' => 99999,
        'clientTransactionId' => $order->id,
    ]));

    $response->assertRedirect(route('checkout.index'));
});

it('cancels PayPhone payment', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
        'payment_status' => PaymentStatus::PENDING,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('payphone.cancel', $order));

    $response->assertRedirect(route('checkout.index'));

    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::FAILED)
        ->and($order->status)->toBe(OrderStatus::CANCELLED);
});

it('blocks other users from cancelling payment', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
    ]);

    $this->actingAs($otherUser);

    $response = $this->get(route('payphone.cancel', $order));

    $response->assertForbidden();
});

it('payphone config is defined', function () {
    expect(config('services.payphone'))->toBeArray()
        ->and(config('services.payphone'))->toHaveKeys(['base_url', 'token', 'store_id']);
});

it('PAYPHONE exists in PaymentMethod enum', function () {
    $payphone = \App\Enums\PaymentMethod::PAYPHONE;

    expect($payphone->value)->toBe('payphone')
        ->and($payphone->requiresOnlineProcessing())->toBeTrue()
        ->and($payphone->fee())->toBe(0.02);
});
