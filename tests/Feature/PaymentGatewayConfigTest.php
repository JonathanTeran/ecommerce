<?php

use App\Enums\Module;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\NuveiService;
use App\Services\PayPhoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

    $this->tenant = Tenant::factory()->create(['slug' => 'gateway-test-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise-gw-' . uniqid(),
        'type' => 'enterprise', 'price' => 150,
        'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);
});

it('stores payment gateway config in GeneralSetting', function () {
    $settings = GeneralSetting::create([
        'site_name' => 'Test Store',
        'tenant_id' => $this->tenant->id,
        'payment_gateways_config' => [
            'nuvei_enabled' => true,
            'nuvei_merchant_id' => 'MID-123',
            'nuvei_site_id' => 'SID-456',
            'nuvei_secret_key' => 'SK-789',
            'nuvei_environment' => 'production',
            'payphone_enabled' => true,
            'payphone_token' => 'PPT-ABC',
            'payphone_store_id' => 'PPSI-DEF',
            'payphone_environment' => 'test',
        ],
    ]);

    expect($settings->payment_gateways_config)->toBeArray()
        ->and($settings->isGatewayEnabled('nuvei'))->toBeTrue()
        ->and($settings->isGatewayEnabled('payphone'))->toBeTrue()
        ->and($settings->isGatewayEnabled('stripe'))->toBeFalse();
});

it('returns gateway-specific config', function () {
    $settings = GeneralSetting::create([
        'site_name' => 'Test Store',
        'tenant_id' => $this->tenant->id,
        'payment_gateways_config' => [
            'nuvei_enabled' => true,
            'nuvei_merchant_id' => 'MID-TEST',
            'nuvei_site_id' => 'SID-TEST',
            'nuvei_secret_key' => 'SK-TEST',
            'nuvei_environment' => 'production',
            'payphone_enabled' => false,
        ],
    ]);

    $nuveiConfig = $settings->getGatewayConfig('nuvei');

    expect($nuveiConfig['enabled'])->toBeTrue()
        ->and($nuveiConfig['merchant_id'])->toBe('MID-TEST')
        ->and($nuveiConfig['site_id'])->toBe('SID-TEST')
        ->and($nuveiConfig['secret_key'])->toBe('SK-TEST')
        ->and($nuveiConfig['environment'])->toBe('production');
});

it('returns default config when no gateways configured', function () {
    $settings = GeneralSetting::create([
        'site_name' => 'Empty Store',
        'tenant_id' => $this->tenant->id,
    ]);

    $config = $settings->getPaymentGatewaysConfig();

    expect($config['nuvei_enabled'])->toBeFalse()
        ->and($config['payphone_enabled'])->toBeFalse()
        ->and($config['bank_transfer_enabled'])->toBeFalse();
});

it('NuveiService uses tenant config when available', function () {
    GeneralSetting::create([
        'site_name' => 'Tenant Store',
        'tenant_id' => $this->tenant->id,
        'payment_gateways_config' => [
            'nuvei_enabled' => true,
            'nuvei_merchant_id' => 'TENANT-MID',
            'nuvei_site_id' => 'TENANT-SID',
            'nuvei_secret_key' => 'TENANT-SK',
            'nuvei_environment' => 'test',
        ],
    ]);

    $service = new NuveiService;

    // Service should have loaded tenant config
    $reflection = new ReflectionClass($service);

    $merchantProp = $reflection->getProperty('merchantId');
    $merchantProp->setAccessible(true);
    expect($merchantProp->getValue($service))->toBe('TENANT-MID');

    $siteProp = $reflection->getProperty('siteId');
    $siteProp->setAccessible(true);
    expect($siteProp->getValue($service))->toBe('TENANT-SID');
});

it('PayPhoneService uses tenant config when available', function () {
    GeneralSetting::create([
        'site_name' => 'Tenant Store',
        'tenant_id' => $this->tenant->id,
        'payment_gateways_config' => [
            'payphone_enabled' => true,
            'payphone_token' => 'TENANT-TOKEN',
            'payphone_store_id' => 'TENANT-STORE',
            'payphone_environment' => 'test',
        ],
    ]);

    $service = new PayPhoneService;

    $reflection = new ReflectionClass($service);

    $tokenProp = $reflection->getProperty('token');
    $tokenProp->setAccessible(true);
    expect($tokenProp->getValue($service))->toBe('TENANT-TOKEN');

    $storeProp = $reflection->getProperty('storeId');
    $storeProp->setAccessible(true);
    expect($storeProp->getValue($service))->toBe('TENANT-STORE');
});

it('services fallback to global config when gateway not enabled', function () {
    GeneralSetting::create([
        'site_name' => 'Basic Store',
        'tenant_id' => $this->tenant->id,
        'payment_gateways_config' => [
            'nuvei_enabled' => false,
            'payphone_enabled' => false,
        ],
    ]);

    // NuveiService should fallback to global config (empty when no env vars)
    $nuveiService = new NuveiService;
    $reflection = new ReflectionClass($nuveiService);
    $merchantProp = $reflection->getProperty('merchantId');
    $merchantProp->setAccessible(true);
    // Falls back to config, which is empty string if no env vars set
    expect($merchantProp->getValue($nuveiService))->toBeString();

    // PayPhoneService should fallback to empty (no global config set)
    $ppService = new PayPhoneService;
    $reflection = new ReflectionClass($ppService);
    $tokenProp = $reflection->getProperty('token');
    $tokenProp->setAccessible(true);
    expect($tokenProp->getValue($ppService))->toBe('');
});

it('admin can access settings page', function () {
    GeneralSetting::create([
        'site_name' => 'Test',
        'tenant_id' => $this->tenant->id,
    ]);

    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');

    \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

    $this->actingAs($admin)
        ->get(\App\Filament\Pages\ManageGeneralSettings::getUrl())
        ->assertSuccessful();
});
