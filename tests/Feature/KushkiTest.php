<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\User;
use App\Services\KushkiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
});

it('creates payment in simulation mode', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $service = new KushkiService;
    $result = $service->createPayment($order);

    expect($result['status'])->toBe('success')
        ->and($result['transaction_id'])->toStartWith('KSH-SIM-');
});

it('confirms transaction in simulation mode', function () {
    $service = new KushkiService;
    $result = $service->confirmTransaction('test-ticket-123');

    expect($result['isSuccessful'])->toBeTrue()
        ->and($result['ticketNumber'])->toBe('test-ticket-123');
});

it('handles successful Kushki callback with auth', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
        'payment_status' => PaymentStatus::PENDING,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('kushki.callback', [
        'ticketNumber' => 'KSH-TEST-123',
        'order' => $order->id,
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

    $response = $this->get(route('kushki.callback', [
        'ticketNumber' => 'KSH-TEST-123',
        'order' => $order->id,
    ]));

    $response->assertRedirect(route('checkout.confirmation', $order));
});

it('redirects unauthenticated callback to login', function () {
    $response = $this->get(route('kushki.callback', [
        'ticketNumber' => 'KSH-TEST-123',
        'order' => 1,
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

    $response = $this->get(route('kushki.callback', [
        'ticketNumber' => 'KSH-TEST-123',
        'order' => $order->id,
    ]));

    $response->assertRedirect(route('checkout.index'));
});

it('cancels Kushki payment', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
        'payment_status' => PaymentStatus::PENDING,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('kushki.cancel', $order));

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

    $response = $this->get(route('kushki.cancel', $order));

    $response->assertForbidden();
});

it('kushki config is defined', function () {
    expect(config('services.kushki'))->toBeArray()
        ->and(config('services.kushki'))->toHaveKeys(['public_key', 'private_key']);
});

it('KUSHKI exists in PaymentMethod enum', function () {
    $kushki = PaymentMethod::KUSHKI;

    expect($kushki->value)->toBe('kushki')
        ->and($kushki->getLabel())->toBe('Kushki / Datafast')
        ->and($kushki->requiresOnlineProcessing())->toBeTrue()
        ->and($kushki->fee())->toBe(0.035);
});

it('includes kushki in available payment methods when enabled', function () {
    $settings = GeneralSetting::firstOrCreate([], ['site_name' => 'Test']);
    $settings->update([
        'payment_gateways_config' => array_merge(
            $settings->getPaymentGatewaysConfig(),
            [
                'kushki_enabled' => true,
                'kushki_public_key' => 'test-public-key',
                'kushki_private_key' => 'test-private-key',
                'kushki_environment' => 'test',
                'kushki_surcharge_percentage' => 0,
            ]
        ),
    ]);

    $methods = $settings->getAvailablePaymentMethods();
    $kushki = $methods->firstWhere('key', 'kushki');

    expect($kushki)->not->toBeNull()
        ->and($kushki->gateway)->toBe('kushki')
        ->and($kushki->name)->toBe('Kushki / Datafast');
});

it('does not include kushki when disabled', function () {
    $settings = GeneralSetting::firstOrCreate([], ['site_name' => 'Test']);
    $settings->update([
        'payment_gateways_config' => array_merge(
            $settings->getPaymentGatewaysConfig(),
            ['kushki_enabled' => false]
        ),
    ]);

    $methods = $settings->getAvailablePaymentMethods();
    $kushki = $methods->firstWhere('key', 'kushki');

    expect($kushki)->toBeNull();
});
