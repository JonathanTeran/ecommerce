<?php

use App\Enums\Module;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['is_default' => true]);
    $this->plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $this->plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);

    $this->category = Category::create([
        'name' => 'Electronics', 'slug' => 'electronics',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => true, 'position' => 0,
    ]);
    $this->brand = Brand::create([
        'name' => 'Apple', 'slug' => 'apple',
        'tenant_id' => $this->tenant->id, 'is_active' => true,
        'is_featured' => false, 'position' => 0,
    ]);
});

it('shows quotation-only view when quotation_only_mode is enabled', function () {
    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
        'tax_rate' => 15.00,
        'payment_gateways_config' => [
            'quotation_only_mode' => true,
        ],
    ]);

    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($user)->get('/checkout');

    $response->assertSuccessful();
    $response->assertSee('Solicitar Cotización');
    $response->assertDontSee('Transferencia Bancaria');
    $response->assertDontSee('Pago Contra Entrega');
});

it('shows payment methods when quotation_only_mode is disabled', function () {
    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
        'tax_rate' => 15.00,
        'payment_gateways_config' => [
            'quotation_only_mode' => false,
            'bank_transfer_enabled' => true,
            'bank_transfer_instructions' => 'Banco Pichincha - Cuenta 123',
        ],
    ]);

    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($user)->get('/checkout');

    $response->assertSuccessful();
    $response->assertSee('Transferencia Bancaria');
});

it('correctly reports quotation only mode status', function () {
    $settings = GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
        'tax_rate' => 15.00,
    ]);

    expect($settings->isQuotationOnlyMode())->toBeFalse();

    $settings->update([
        'payment_gateways_config' => ['quotation_only_mode' => true],
    ]);

    expect($settings->fresh()->isQuotationOnlyMode())->toBeTrue();
});
