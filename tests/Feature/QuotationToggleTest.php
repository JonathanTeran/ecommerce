<?php

use App\Enums\QuotationStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

    $this->tenant = Tenant::factory()->create(['is_default' => true]);
    app()->instance('current_tenant', $this->tenant);
});

function createToggleProduct(): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'toggle-cat'],
        ['name' => 'Test Category']
    );

    return Product::create([
        'name' => 'Toggle Product',
        'slug' => 'toggle-product-' . uniqid(),
        'sku' => 'TG-' . strtoupper(uniqid()),
        'price' => 100,
        'quantity' => 20,
        'category_id' => $category->id,
    ]);
}

function disableQuotations(): void
{
    $settings = GeneralSetting::firstOrCreate([], ['site_name' => 'Test']);
    $config = $settings->getPaymentGatewaysConfig();
    $config['quotations_enabled'] = false;
    $settings->update(['payment_gateways_config' => $config]);
}

function enableQuotations(): void
{
    $settings = GeneralSetting::firstOrCreate([], ['site_name' => 'Test']);
    $config = $settings->getPaymentGatewaysConfig();
    $config['quotations_enabled'] = true;
    $settings->update(['payment_gateways_config' => $config]);
}

// ═══════════════════════════════════════════
// Default Behavior
// ═══════════════════════════════════════════

it('quotations_enabled defaults to true', function () {
    $settings = GeneralSetting::firstOrCreate([], ['site_name' => 'Test']);

    expect($settings->isQuotationsEnabled())->toBeTrue();
});

// ═══════════════════════════════════════════
// API Endpoint
// ═══════════════════════════════════════════

it('blocks API quotation creation when disabled', function () {
    disableQuotations();

    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $cart->addItem(createToggleProduct(), 1);

    $this->actingAs($user)
        ->postJson('/api/quotation', [
            'customer_name' => 'Test',
            'customer_email' => 'test@test.com',
            'customer_phone' => '0991234567',
            'shipping_address' => [
                'name' => 'Test', 'address' => 'Test', 'city' => 'Test',
                'state' => 'Test', 'zip' => '12345', 'phone' => '0991234567',
            ],
        ])
        ->assertForbidden()
        ->assertJson(['message' => 'Las cotizaciones no están disponibles en esta tienda.']);
});

it('allows API quotation creation when enabled', function () {
    enableQuotations();

    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $cart->addItem(createToggleProduct(), 1);

    $this->actingAs($user)
        ->postJson('/api/quotation', [
            'customer_name' => 'Test',
            'customer_email' => 'test@test.com',
            'customer_phone' => '0991234567',
            'shipping_address' => [
                'name' => 'Test', 'address' => 'Test', 'city' => 'Test',
                'state' => 'Test', 'zip' => '12345', 'phone' => '0991234567',
            ],
        ])
        ->assertSuccessful();
});

// ═══════════════════════════════════════════
// Web Routes
// ═══════════════════════════════════════════

it('blocks quotation create page when disabled', function () {
    disableQuotations();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('quotation.create'))
        ->assertNotFound();
});

it('allows quotation create page when enabled', function () {
    enableQuotations();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('quotation.create'))
        ->assertSuccessful();
});

it('still allows viewing existing quotations when disabled', function () {
    $user = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
    ]);

    disableQuotations();

    $this->actingAs($user)
        ->get(route('quotations.index'))
        ->assertSuccessful()
        ->assertSee($quotation->quotation_number);

    $this->actingAs($user)
        ->get(route('quotations.show', $quotation))
        ->assertSuccessful()
        ->assertSee($quotation->quotation_number);
});

// ═══════════════════════════════════════════
// Quotation Only Mode Override
// ═══════════════════════════════════════════

it('quotation_only_mode returns false when quotations are disabled', function () {
    $settings = GeneralSetting::firstOrCreate([], ['site_name' => 'Test']);
    $config = $settings->getPaymentGatewaysConfig();
    $config['quotations_enabled'] = false;
    $config['quotation_only_mode'] = true;
    $settings->update(['payment_gateways_config' => $config]);

    expect($settings->fresh()->isQuotationOnlyMode())->toBeFalse();
});

it('quotation_only_mode works when quotations are enabled', function () {
    $settings = GeneralSetting::firstOrCreate([], ['site_name' => 'Test']);
    $config = $settings->getPaymentGatewaysConfig();
    $config['quotations_enabled'] = true;
    $config['quotation_only_mode'] = true;
    $settings->update(['payment_gateways_config' => $config]);

    expect($settings->fresh()->isQuotationOnlyMode())->toBeTrue();
});

// ═══════════════════════════════════════════
// Checkout View
// ═══════════════════════════════════════════

it('hides quotation link in checkout when disabled', function () {
    disableQuotations();

    $settings = GeneralSetting::first();
    $config = $settings->getPaymentGatewaysConfig();
    $config['bank_transfer_enabled'] = true;
    $config['bank_transfer_instructions'] = 'Cuenta: 123456';
    $settings->update(['payment_gateways_config' => $config]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/checkout')
        ->assertSuccessful()
        ->assertDontSee('Solicitar Cotización');
});

it('shows quotation link in checkout when enabled', function () {
    enableQuotations();

    $settings = GeneralSetting::first();
    $config = $settings->getPaymentGatewaysConfig();
    $config['bank_transfer_enabled'] = true;
    $config['bank_transfer_instructions'] = 'Cuenta: 123456';
    $settings->update(['payment_gateways_config' => $config]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/checkout')
        ->assertSuccessful()
        ->assertSee('Solicitar Cotización');
});
