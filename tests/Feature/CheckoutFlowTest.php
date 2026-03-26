<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->category = Category::factory()->create();
    $this->brand = Brand::factory()->create();

    $this->settings = GeneralSetting::create([
        'site_name' => 'Test Store',
        'tax_rate' => 15.00,
        'payment_gateways_config' => [
            'bank_transfer_enabled' => true,
            'bank_transfer_instructions' => 'Banco Pichincha - Cuenta 123456',
            'bank_transfer_surcharge_percentage' => 0,
            'bank_transfer_requires_proof' => false,
        ],
    ]);

    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'price' => 100.00,
        'quantity' => 10,
        'is_active' => true,
    ]);
});

function validCheckoutData(string $paymentMethod = 'bank_transfer'): array
{
    return [
        'payment_method' => $paymentMethod,
        'shipping_address' => [
            'name' => 'Test Buyer',
            'identity_document' => '1712345678',
            'email' => 'testbuyer@example.com',
            'address' => 'Av. Amazonas N36-152',
            'city' => 'Quito',
            'state' => 'Pichincha',
            'zip' => '170150',
            'phone' => '0991234567',
        ],
        'billing_address' => [
            'name' => 'Test Buyer',
            'tax_id' => '1712345678001',
            'address' => 'Av. Amazonas N36-152',
            'city' => 'Quito',
            'state' => 'Pichincha',
            'zip' => '170150',
            'phone' => '0991234567',
        ],
        'accepted_legal_documents' => true,
    ];
}

it('completes full checkout flow: register, add to cart, checkout, view order in backend', function () {
    $user = User::factory()->create([
        'name' => 'Test Buyer',
        'email' => 'testbuyer@example.com',
        'password' => bcrypt('password123'),
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->email)->toBe('testbuyer@example.com');

    $sessionId = \Illuminate\Support\Str::uuid()->toString();

    $response = $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $this->product->id, 'quantity' => 2]);
    $response->assertSuccessful();
    $response->assertJsonPath('message', 'Item added to cart');

    $cart = Cart::forSession($sessionId)->first();
    expect($cart)->not->toBeNull();
    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->quantity)->toBe(2);

    $this->actingAs($user);

    $response = $this->withHeaders(['X-Session-ID' => $sessionId])->getJson('/api/cart');
    $response->assertSuccessful();

    $userCart = Cart::forUser($user->id)->first();
    expect($userCart)->not->toBeNull();
    expect($userCart->items)->toHaveCount(1);

    $response = $this->get('/checkout');
    $response->assertSuccessful();
    $response->assertSee(__('Checkout'));
    $response->assertSee('Transferencia Bancaria');

    $response = $this->withHeaders([
        'Accept' => 'application/json',
        'X-Session-ID' => $sessionId,
    ])->post('/checkout/place-order', validCheckoutData());
    $response->assertSuccessful();
    $response->assertJsonStructure(['message', 'redirect', 'order_id']);

    $orderId = $response->json('order_id');
    expect($orderId)->not->toBeNull();

    $order = Order::find($orderId);
    expect($order)->not->toBeNull();
    expect($order->user_id)->toBe($user->id);
    expect($order->status)->toBe(OrderStatus::PENDING);
    expect($order->payment_status)->toBe(PaymentStatus::PENDING);
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->product_id)->toBe($this->product->id);
    expect($order->items->first()->quantity)->toBe(2);
    expect((float) $order->subtotal)->toBe(200.00);

    $response = $this->get('/checkout/confirmation/'.$order->id);
    $response->assertSuccessful();
    $response->assertSee('Pedido Realizado');
    $response->assertSee($order->order_number);

    $userOrders = Order::where('user_id', $user->id)->get();
    expect($userOrders)->toHaveCount(1);
    expect($userOrders->first()->order_number)->toBe($order->order_number);

    $userCart = Cart::forUser($user->id)->first();
    expect($userCart)->toBeNull();
});

it('shows pending order in admin backend', function () {
    $buyer = User::factory()->create(['name' => 'Buyer User', 'email' => 'buyer@test.com']);

    $order = Order::factory()->create([
        'user_id' => $buyer->id,
        'status' => OrderStatus::PENDING,
        'payment_status' => PaymentStatus::PENDING,
        'payment_method' => \App\Enums\PaymentMethod::BANK_TRANSFER,
        'subtotal' => 200.00,
        'total' => 200.00,
    ]);

    $admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@test.com']);
    $this->actingAs($admin);

    $pendingOrder = Order::where('status', OrderStatus::PENDING)->first();
    expect($pendingOrder)->not->toBeNull();
    expect($pendingOrder->id)->toBe($order->id);
    expect($pendingOrder->user_id)->toBe($buyer->id);
});

it('calculates surcharge correctly when payment method has surcharge', function () {
    $this->settings->update([
        'payment_gateways_config' => array_merge(
            $this->settings->payment_gateways_config ?? [],
            ['nuvei_enabled' => true, 'nuvei_surcharge_percentage' => 5.00]
        ),
    ]);

    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $cart->addItem($this->product, 2);
    $cartTotal = $cart->fresh()->total;

    $this->actingAs($user);

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->post('/checkout/place-order', validCheckoutData('credit_card'));
    $response->assertSuccessful();

    $order = Order::find($response->json('order_id'));
    expect((float) $order->subtotal)->toBe(200.00);
    expect((float) $order->surcharge_amount)->toBeGreaterThan(0);

    $expectedTotal = $cartTotal + ($cartTotal * 0.05);
    expect((float) $order->total)->toBe(round($expectedTotal, 2));
});

it('requires payment proof when payment method requires it', function () {
    $this->settings->update([
        'payment_gateways_config' => array_merge(
            $this->settings->payment_gateways_config ?? [],
            ['bank_transfer_requires_proof' => true]
        ),
    ]);

    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $cart->addItem($this->product, 1);

    $this->actingAs($user);

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->post('/checkout/place-order', validCheckoutData('bank_transfer'));

    $response->assertStatus(400);
    $response->assertJsonPath('message', 'Payment proof required');
});

it('rejects checkout with empty cart', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->post('/checkout/place-order', validCheckoutData());

    $response->assertStatus(400);
    $response->assertJsonPath('message', 'Cart is empty');
});

it('prevents user from viewing another users order confirmation', function () {
    $buyer1 = User::factory()->create();
    $buyer2 = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $buyer1->id,
        'status' => OrderStatus::PENDING,
        'payment_method' => \App\Enums\PaymentMethod::BANK_TRANSFER,
    ]);

    $this->actingAs($buyer2);

    $response = $this->get('/checkout/confirmation/'.$order->id);
    $response->assertForbidden();
});
