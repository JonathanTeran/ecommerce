<?php

use App\Enums\Module;
use App\Filament\Pages\ManageNotificationSettings;
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

    $this->tenant = Tenant::factory()->create();
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render the notification settings page', function () {
    Livewire::test(ManageNotificationSettings::class)
        ->assertSuccessful();
});

it('can save notification configuration', function () {
    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
    ]);

    Livewire::test(ManageNotificationSettings::class)
        ->set('data.notification_config.order_confirmed', true)
        ->set('data.notification_config.abandoned_cart', true)
        ->set('data.notification_config.review_request', false)
        ->call('save');

    $settings = GeneralSetting::first();
    $config = $settings->getNotificationConfig();
    expect($config['order_confirmed'])->toBeTrue();
    expect($config['abandoned_cart'])->toBeTrue();
});

it('loads existing configuration on mount', function () {
    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
        'notification_config' => [
            'order_confirmed' => false,
            'abandoned_cart' => true,
        ],
    ]);

    $page = Livewire::test(ManageNotificationSettings::class);
    $data = $page->get('data');

    expect($data['notification_config']['abandoned_cart'])->toBeTrue();
});

it('has defaults when no config exists', function () {
    $settings = new GeneralSetting;
    $config = $settings->getNotificationConfig();

    expect($config['order_confirmed'])->toBeTrue();
    expect($config['order_shipped'])->toBeTrue();
    expect($config['abandoned_cart'])->toBeFalse();
    expect($config['review_request'])->toBeFalse();
    expect($config['new_order_admin'])->toBeTrue();
});
