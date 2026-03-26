<?php

use App\Helpers\Feature;
use App\Models\FeatureFlag;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::clear();
});

test('feature is disabled if not exists', function () {
    $tenant = Tenant::factory()->create();
    expect(Feature::isEnabled('non-existent', $tenant))->toBeFalse();
});

test('feature is globally enabled', function () {
    $tenant = Tenant::factory()->create();
    
    FeatureFlag::create([
        'name' => 'Test Global',
        'key' => 'global-feat',
        'is_enabled_globally' => true,
    ]);

    expect(Feature::isEnabled('global-feat', $tenant))->toBeTrue();
});

test('feature is globally enabled but tenant is disabled', function () {
    $tenant = Tenant::factory()->create();
    
    FeatureFlag::create([
        'name' => 'Test Global Tenant Disabled',
        'key' => 'global-feat-disabled',
        'is_enabled_globally' => true,
        'disabled_tenants' => [(string) $tenant->id],
    ]);

    expect(Feature::isEnabled('global-feat-disabled', $tenant))->toBeFalse();
});

test('feature is globally disabled but tenant is enabled', function () {
    $tenant = Tenant::factory()->create();
    
    FeatureFlag::create([
        'name' => 'Test Specific',
        'key' => 'specific-feat',
        'is_enabled_globally' => false,
        'enabled_tenants' => [(string) $tenant->id],
    ]);

    expect(Feature::isEnabled('specific-feat', $tenant))->toBeTrue();
});

test('cache is invalidated when feature changes', function () {
    $tenant = Tenant::factory()->create();
    
    $flag = FeatureFlag::create([
        'name' => 'Cache Test',
        'key' => 'cache-test',
        'is_enabled_globally' => true,
    ]);

    expect(Feature::isEnabled('cache-test', $tenant))->toBeTrue();

    // Update the flag without explicitly clearing cache, should trigger observer
    $flag->update(['is_enabled_globally' => false]);

    expect(Feature::isEnabled('cache-test', $tenant))->toBeFalse();
});
