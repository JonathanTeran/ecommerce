<?php

use App\Enums\PaymentMethod;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Services\SriService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('maps payment methods to sri payment codes', function (PaymentMethod $method, string $expectedCode) {
    GeneralSetting::create([]);

    $order = Order::factory()->create([
        'payment_method' => $method,
        'subtotal' => 100.00,
        'tax_amount' => 15.00,
        'shipping_amount' => 0.00,
        'discount_amount' => 0.00,
        'total' => 115.00,
        'currency' => 'USD',
        'billing_address' => [
            'tax_id' => '1207481803',
            'name' => 'Cliente',
            'address' => 'Calle Principal',
            'city' => 'Guayaquil',
        ],
    ]);

    $service = new SriService;
    $accessKey = $service->generateAccessKey($order);

    $xmlString = $service->generateInvoiceXml($order, $accessKey);

    $xml = new SimpleXMLElement($xmlString);

    expect((string) $xml->infoFactura->pagos->pago->formaPago)->toBe($expectedCode);
})->with([
    'cash_on_delivery' => [PaymentMethod::CASH_ON_DELIVERY, '01'],
    'debit_card' => [PaymentMethod::DEBIT_CARD, '16'],
    'credit_card' => [PaymentMethod::CREDIT_CARD, '19'],
    'payphone' => [PaymentMethod::PAYPHONE, '17'],
    'paypal' => [PaymentMethod::PAYPAL, '20'],
    'bank_transfer' => [PaymentMethod::BANK_TRANSFER, '20'],
]);
