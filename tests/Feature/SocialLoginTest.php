<?php

use App\Enums\Module;
use App\Filament\Pages\ManageSocialLogin;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer']);

    $this->tenant = Tenant::factory()->create(['slug' => 'social-login-test']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise-social-' . uniqid(),
        'type' => 'enterprise', 'price' => 150,
        'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);

    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
        'social_login_config' => [
            'google' => ['enabled' => true, 'client_id' => 'test-google-id', 'client_secret' => 'test-google-secret'],
            'facebook' => ['enabled' => false, 'client_id' => '', 'client_secret' => ''],
            'apple' => ['enabled' => false, 'client_id' => '', 'client_secret' => ''],
        ],
    ]);
});

it('has social login config in general settings', function () {
    $settings = GeneralSetting::first();
    $config = $settings->getSocialLoginConfig();

    expect($config)->toHaveKey('google')
        ->and($config['google']['enabled'])->toBeTrue()
        ->and($config['google']['client_id'])->toBe('test-google-id');
});

it('returns default social login config when none configured', function () {
    $emptyTenant = Tenant::factory()->create(['slug' => 'empty-store']);
    $settings = GeneralSetting::create([
        'tenant_id' => $emptyTenant->id,
        'site_name' => 'Empty Store',
    ]);

    $config = $settings->getSocialLoginConfig();

    expect($config['google']['enabled'])->toBeFalse()
        ->and($config['facebook']['enabled'])->toBeFalse()
        ->and($config['apple']['enabled'])->toBeFalse();
});

it('rejects invalid social providers', function () {
    $response = $this->get('/auth/invalid-provider/redirect');

    $response->assertNotFound();
});

it('stores social login fields on user', function () {
    $user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'social_provider' => 'google',
        'social_id' => '123456',
        'social_avatar' => 'https://example.com/avatar.jpg',
    ]);

    expect($user->social_provider)->toBe('google')
        ->and($user->social_id)->toBe('123456')
        ->and($user->social_avatar)->toBe('https://example.com/avatar.jpg');
});

it('can render the social login config page for admin', function () {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(ManageSocialLogin::class)
        ->assertSuccessful();
});

it('can save social login config', function () {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(ManageSocialLogin::class)
        ->set('data.social_login_config.facebook.enabled', true)
        ->set('data.social_login_config.facebook.client_id', 'fb-app-id')
        ->set('data.social_login_config.facebook.client_secret', 'fb-app-secret')
        ->call('save')
        ->assertHasNoErrors();

    $settings = GeneralSetting::where('tenant_id', $this->tenant->id)->first();
    $config = $settings->getSocialLoginConfig();

    expect($config['facebook']['enabled'])->toBeTrue()
        ->and($config['facebook']['client_id'])->toBe('fb-app-id');
});
