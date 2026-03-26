<?php

use App\Enums\PaymentMethod;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\SriService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('includes pagos with plazo for factura version 1.1.0', function () {
    config(['sri.invoice_version' => '1.1.0']);
    GeneralSetting::create([]);

    $order = Order::factory()->create([
        'subtotal' => 10.00,
        'discount_amount' => 0,
        'tax_amount' => 1.50,
        'shipping_amount' => 0,
        'total' => 11.50,
        'payment_method' => PaymentMethod::BANK_TRANSFER,
        'billing_address' => [
            'tax_id' => '1207481803',
            'name' => 'Eduardo',
            'address' => 'LOS TULIPANES',
            'city' => 'GUAYAQUIL',
        ],
    ]);

    OrderItem::factory()->for($order)->create([
        'sku' => 'PROD-00017',
        'name' => 'AUDIFONOS 2.0',
        'price' => 10.00,
        'quantity' => 1,
        'subtotal' => 10.00,
    ]);

    $service = new SriService;
    $xml = $service->generateInvoiceXml($order, $service->generateAccessKey($order));

    expect($xml)
        ->toContain('<pagos>')
        ->toContain('<plazo>0</plazo>')
        ->toContain('<unidadTiempo>dias</unidadTiempo>');
});

it('uses order currency and includes pagos for factura version 2.1.0', function () {
    config(['sri.invoice_version' => '2.1.0']);
    GeneralSetting::create([]);

    $order = Order::factory()->create([
        'subtotal' => 10.00,
        'discount_amount' => 0,
        'tax_amount' => 1.50,
        'shipping_amount' => 0,
        'total' => 11.50,
        'payment_method' => PaymentMethod::BANK_TRANSFER,
        'billing_address' => [
            'tax_id' => '1207481803',
            'name' => 'Eduardo',
            'address' => 'LOS TULIPANES',
            'city' => 'GUAYAQUIL',
        ],
    ]);

    OrderItem::factory()->for($order)->create([
        'sku' => 'PROD-00017',
        'name' => 'AUDIFONOS 2.0',
        'price' => 10.00,
        'quantity' => 1,
        'subtotal' => 10.00,
    ]);

    $service = new SriService;
    $xml = $service->generateInvoiceXml($order, $service->generateAccessKey($order));

    expect($xml)
        ->toContain('<moneda>USD</moneda>')
        ->toContain('<pagos>')
        ->toContain('<plazo>0</plazo>')
        ->toContain('<unidadTiempo>dias</unidadTiempo>');
});
