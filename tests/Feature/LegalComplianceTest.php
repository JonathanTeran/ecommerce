<?php

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

    GeneralSetting::create([
        'site_name' => 'Test Store',
        'tax_rate' => 15.00,
        'payment_gateways_config' => [
            'bank_transfer_enabled' => true,
            'bank_transfer_requires_proof' => false,
        ],
    ]);

    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'price' => 100.00,
        'quantity' => 20,
        'is_active' => true,
    ]);
});

it('renders legal saas pages', function () {
    $this->get(route('legal.terms'))
        ->assertSuccessful()
        ->assertSee('Términos de Servicio SaaS')
        ->assertSee('AmePhia')
        ->assertSee('https://amephia.com/')
        ->assertDontSee('operada por Laravel')
        ->assertSee('periodo de gracia de 30 días')
        ->assertSee('deshabilitada automáticamente')
        ->assertSee('Operación global y jurisdicciones restringidas')
        ->assertSee('Ley aplicable y resolución de disputas')
        ->assertSee('Propiedad intelectual')
        ->assertSee('infracción de derechos de autor o marca');

    $this->get(route('legal.privacy'))
        ->assertSuccessful()
        ->assertSee('Política de Privacidad');

    $this->get(route('legal.acceptable-use'))
        ->assertSuccessful()
        ->assertSee('Política de Uso Aceptable')
        ->assertSee('Infracciones de propiedad intelectual');
});

it('requires legal acceptance to place an order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cart = Cart::create(['user_id' => $user->id]);
    $cart->addItem($this->product, 1);

    $response = $this->postJson('/checkout/place-order', checkoutPayload(
        acceptsLegalDocuments: false
    ));

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['accepted_legal_documents']);
});

it('stores legal acceptance evidence on order creation', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cart = Cart::create(['user_id' => $user->id]);
    $cart->addItem($this->product, 1);

    $response = $this->postJson('/checkout/place-order', checkoutPayload(
        acceptsLegalDocuments: true
    ));

    $response->assertSuccessful();

    $order = Order::query()->find($response->json('order_id'));

    expect($order)->not->toBeNull();
    expect($order->legal_acceptance)->toBeArray();
    expect(data_get($order->legal_acceptance, 'documents.terms_of_service.version'))
        ->toBe(config('legal.terms.version'));
    expect(data_get($order->legal_acceptance, 'documents.privacy_policy.version'))
        ->toBe(config('legal.privacy.version'));
    expect(data_get($order->legal_acceptance, 'documents.acceptable_use_policy.version'))
        ->toBe(config('legal.acceptable_use.version'));
    expect(data_get($order->legal_acceptance, 'ip_address'))->toBe('127.0.0.1');
});

function checkoutPayload(bool $acceptsLegalDocuments, string $paymentMethod = 'bank_transfer'): array
{
    return [
        'payment_method' => $paymentMethod,
        'accepted_legal_documents' => $acceptsLegalDocuments ? '1' : '0',
        'shipping_address' => [
            'name' => 'Cliente Test',
            'identity_document' => '1712345678',
            'email' => 'cliente@test.com',
            'address' => 'Av. Principal 123',
            'city' => 'Quito',
            'state' => 'Pichincha',
            'zip' => '170101',
            'phone' => '0999999999',
        ],
        'billing_address' => [
            'name' => 'Cliente Test',
            'tax_id' => '1712345678',
            'address' => 'Av. Principal 123',
            'city' => 'Quito',
            'state' => 'Pichincha',
            'zip' => '170101',
            'phone' => '0999999999',
        ],
    ];
}
