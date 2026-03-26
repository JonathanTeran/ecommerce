<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('downloads sri xml when stored', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'sri_access_key' => '2001202601120748180300110020010000000311234567814',
        'sri_xml_path' => 'xml/facturas/2001202601120748180300110020010000000311234567814.xml',
    ]);

    Storage::disk('local')->put($order->sri_xml_path, '<xml>ok</xml>');

    $response = $this->actingAs($user)->get(route('admin.orders.sri-xml', $order));

    $response->assertOk();

    expect($response->headers->get('content-disposition'))
        ->toContain('factura-'.$order->sri_access_key.'.xml');
});

it('downloads authorized sri xml when available', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'sri_access_key' => '2001202601120748180300110020010000000311234567814',
        'sri_xml_path' => 'xml/facturas/2001202601120748180300110020010000000311234567814.xml',
        'sri_authorized_xml_path' => 'xml/facturas/autorizadas/2001202601120748180300110020010000000311234567814.xml',
    ]);

    Storage::disk('local')->put($order->sri_xml_path, '<xml>signed</xml>');
    Storage::disk('local')->put($order->sri_authorized_xml_path, '<xml>authorized</xml>');

    $response = $this->actingAs($user)->get(route('admin.orders.sri-xml', $order));

    $response->assertOk();
    expect($response->streamedContent())->toContain('authorized');
});

it('returns not found when sri xml is missing', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'sri_xml_path' => null,
        'sri_authorized_xml_path' => null,
    ]);

    $response = $this->actingAs($user)->get(route('admin.orders.sri-xml', $order));

    $response->assertNotFound();
});

it('returns not found when sri xml file does not exist', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'sri_access_key' => '2001202601120748180300110020010000000311234567814',
        'sri_xml_path' => 'xml/facturas/2001202601120748180300110020010000000311234567814.xml',
    ]);

    $response = $this->actingAs($user)->get(route('admin.orders.sri-xml', $order));

    $response->assertNotFound();
});

it('downloads ride pdf with sri details', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'sri_access_key' => '2001202601120748180300110020010000000311234567814',
        'sri_authorization_status' => 'authorized',
        'sri_authorization_number' => '1234567890',
        'sri_authorization_date' => now(),
        'billing_address' => [
            'name' => 'Juan Perez',
            'tax_id' => '1207481803',
            'address' => 'Calle Principal',
            'city' => 'Guayaquil',
            'state' => 'Guayas',
            'phone' => '0999999999',
        ],
        'shipping_address' => [
            'name' => 'Juan Perez',
            'identity_document' => '1207481803',
            'address' => 'Calle Principal',
            'city' => 'Guayaquil',
            'state' => 'Guayas',
            'phone' => '0999999999',
        ],
    ]);

    $response = $this->actingAs($user)->get(route('admin.orders.ride', $order));

    $response->assertOk();

    expect($response->headers->get('content-disposition'))
        ->toContain('ride-'.$order->sri_access_key.'.pdf');
    expect($response->headers->get('content-type'))
        ->toContain('application/pdf');
    expect(str_starts_with($response->getContent(), '%PDF'))->toBeTrue();
});
