<?php

use App\Enums\OrderStatus;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('sends shipped email when order status changes to shipped', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PROCESSING,
    ]);

    $order->updateStatus(OrderStatus::SHIPPED);

    Mail::assertQueued(OrderShippedMail::class, function ($mail) use ($order) {
        return $mail->order->id === $order->id;
    });
});

it('sends delivered email when order status changes to delivered', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::SHIPPED,
    ]);

    $order->updateStatus(OrderStatus::DELIVERED);

    Mail::assertQueued(OrderDeliveredMail::class, function ($mail) use ($order) {
        return $mail->order->id === $order->id;
    });
});

it('sends cancelled email when order status changes to cancelled', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
        'cancellation_reason' => 'Out of stock',
    ]);

    $order->updateStatus(OrderStatus::CANCELLED);

    Mail::assertQueued(OrderCancelledMail::class, function ($mail) use ($order) {
        return $mail->order->id === $order->id;
    });
});

it('does not send email for confirmed status change', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
    ]);

    $order->updateStatus(OrderStatus::CONFIRMED);

    Mail::assertNotQueued(OrderShippedMail::class);
    Mail::assertNotQueued(OrderDeliveredMail::class);
    Mail::assertNotQueued(OrderCancelledMail::class);
});

it('sets shipped_at timestamp when shipped', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PROCESSING,
    ]);

    $order->updateStatus(OrderStatus::SHIPPED);

    expect($order->fresh()->shipped_at)->not->toBeNull();
});

it('sets delivered_at timestamp when delivered', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::SHIPPED,
    ]);

    $order->updateStatus(OrderStatus::DELIVERED);

    expect($order->fresh()->delivered_at)->not->toBeNull();
});

it('sets cancelled_at timestamp when cancelled', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
    ]);

    $order->updateStatus(OrderStatus::CANCELLED);

    expect($order->fresh()->cancelled_at)->not->toBeNull();
});

it('does not transition to invalid status', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::DELIVERED,
    ]);

    $result = $order->updateStatus(OrderStatus::SHIPPED);

    expect($result)->toBeFalse();
    expect($order->fresh()->status)->toBe(OrderStatus::DELIVERED);
    Mail::assertNotQueued(OrderShippedMail::class);
});

it('constructs shipped mail with correct subject', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'order_number' => 'CP-260305-0001']);

    $mail = new OrderShippedMail($order);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('CP-260305-0001');
    expect($envelope->subject)->toContain('enviado');
});

it('constructs delivered mail with correct subject', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'order_number' => 'CP-260305-0002']);

    $mail = new OrderDeliveredMail($order);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('CP-260305-0002');
    expect($envelope->subject)->toContain('entregado');
});

it('constructs cancelled mail with correct subject', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'order_number' => 'CP-260305-0003']);

    $mail = new OrderCancelledMail($order);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('CP-260305-0003');
    expect($envelope->subject)->toContain('cancelado');
});
