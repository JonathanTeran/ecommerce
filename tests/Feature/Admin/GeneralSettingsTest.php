<?php

use App\Enums\Module;
use App\Filament\Pages\ManageGeneralSettings;
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

it('can render general settings page', function () {
    $this->get('/admin/manage-general-settings')->assertSuccessful();
});

it('settings page creates default settings on mount', function () {
    Livewire::test(ManageGeneralSettings::class);

    expect(GeneralSetting::count())->toBeGreaterThanOrEqual(1);
});

it('general setting model stores site name and tax rate', function () {
    $setting = GeneralSetting::create([
        'site_name' => 'Mi Tienda',
        'tax_rate' => 12.0,
        'theme_color' => 'indigo',
        'tenant_id' => $this->tenant->id,
    ]);

    expect($setting->site_name)->toBe('Mi Tienda')
        ->and($setting->tax_rate)->toBe(12.0);
});

it('settings are scoped per tenant', function () {
    GeneralSetting::create([
        'site_name' => 'Tenant A', 'tenant_id' => $this->tenant->id,
        'theme_color' => 'indigo',
    ]);

    $otherTenant = Tenant::factory()->create();
    GeneralSetting::withoutGlobalScopes()->create([
        'site_name' => 'Tenant B', 'tenant_id' => $otherTenant->id,
        'theme_color' => 'emerald',
    ]);

    $visibleSettings = GeneralSetting::all();
    expect($visibleSettings->count())->toBe(1)
        ->and($visibleSettings->first()->site_name)->toBe('Tenant A');
});
