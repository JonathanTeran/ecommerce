<?php

use App\Enums\Module;
use App\Filament\SuperAdmin\Resources\TenantResource\Pages\CreateTenant;
use App\Filament\SuperAdmin\Resources\TenantResource\Pages\EditTenant;
use App\Filament\SuperAdmin\Resources\TenantResource\Pages\ListTenants;
use App\Filament\SuperAdmin\Resources\TenantResource\Pages\ViewTenant;
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
    Filament::setCurrentPanel(Filament::getPanel('super-admin'));
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super_admin');
    $this->actingAs($this->superAdmin);

    $this->plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
});

it('can list tenants', function () {
    Tenant::factory()->create(['name' => 'Tienda Test']);

    Livewire::test(ListTenants::class)->assertSuccessful();
});

it('can view a tenant with an impersonation target', function () {
    $tenant = Tenant::factory()->create(['name' => 'Tienda Test']);
    User::factory()->create(['tenant_id' => $tenant->id]);

    Livewire::test(ViewTenant::class, ['record' => $tenant->getRouteKey()])
        ->assertSuccessful();
});

it('can view a tenant without users', function () {
    $tenant = Tenant::factory()->create(['name' => 'Sin Usuarios']);

    Livewire::test(ViewTenant::class, ['record' => $tenant->getRouteKey()])
        ->assertSuccessful();
});

it('can create tenant with provisioning', function () {
    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Nueva Tienda',
            'slug' => 'nueva-tienda',
            'domain' => 'nueva.example.com',
            'theme_color' => 'emerald',
            'is_active' => true,
            'plan_id' => $this->plan->id,
            'admin_name' => 'Admin Test',
            'admin_email' => 'admin@nueva.com',
            'admin_password' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tenant = Tenant::where('slug', 'nueva-tienda')->first();
    expect($tenant)->not->toBeNull()
        ->and($tenant->name)->toBe('Nueva Tienda')
        ->and($tenant->domain)->toBe('nueva.example.com');

    expect(GeneralSetting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->exists())->toBeTrue();
    expect(Subscription::where('tenant_id', $tenant->id)->where('status', 'active')->exists())->toBeTrue();
    expect(User::where('email', 'admin@nueva.com')->where('tenant_id', $tenant->id)->exists())->toBeTrue();
});

it('can edit tenant', function () {
    $tenant = Tenant::factory()->create(['name' => 'Original', 'theme_color' => 'indigo']);

    Livewire::test(EditTenant::class, ['record' => $tenant->getRouteKey()])
        ->fillForm(['name' => 'Modificada', 'theme_color' => 'emerald'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($tenant->fresh()->name)->toBe('Modificada')
        ->and($tenant->fresh()->theme_color)->toBe('emerald');
});

it('can suspend tenant with message', function () {
    $tenant = Tenant::factory()->create(['is_active' => true]);

    Livewire::test(EditTenant::class, ['record' => $tenant->getRouteKey()])
        ->fillForm([
            'is_active' => false,
            'suspension_message' => 'Cuenta suspendida por falta de pago.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $tenant->refresh();
    expect($tenant->is_active)->toBeFalse()
        ->and($tenant->suspension_message)->toBe('Cuenta suspendida por falta de pago.');
});

it('can reactivate suspended tenant', function () {
    $tenant = Tenant::factory()->create([
        'is_active' => false,
        'suspension_message' => 'Suspendido',
    ]);

    Livewire::test(EditTenant::class, ['record' => $tenant->getRouteKey()])
        ->fillForm(['is_active' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($tenant->fresh()->is_active)->toBeTrue();
});

it('rejects duplicate slug', function () {
    Tenant::factory()->create(['slug' => 'tienda-uno']);

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Tienda Dos', 'slug' => 'tienda-uno',
            'plan_id' => $this->plan->id,
            'admin_name' => 'Admin', 'admin_email' => 'admin@dos.com', 'admin_password' => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

it('rejects duplicate domain', function () {
    Tenant::factory()->create(['domain' => 'shop.example.com']);

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Otra Tienda', 'slug' => 'otra-tienda',
            'domain' => 'shop.example.com',
            'plan_id' => $this->plan->id,
            'admin_name' => 'Admin', 'admin_email' => 'admin@otra.com', 'admin_password' => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['domain' => 'unique']);
});

it('non super admin cannot access tenant list', function () {
    $regularUser = User::factory()->create();
    $this->actingAs($regularUser);

    $this->get('/super-admin/tenants')->assertForbidden();
});

it('tenant provisioning creates admin with correct role', function () {
    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Role Test', 'slug' => 'role-test',
            'plan_id' => $this->plan->id,
            'admin_name' => 'Role Admin', 'admin_email' => 'role@test.com', 'admin_password' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $admin = User::where('email', 'role@test.com')->first();
    expect($admin->hasRole('admin'))->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull();
});

it('provisioned tenant gets general settings', function () {
    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Settings Test', 'slug' => 'settings-test',
            'plan_id' => $this->plan->id,
            'admin_name' => 'Admin', 'admin_email' => 'admin@settings.com', 'admin_password' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tenant = Tenant::where('slug', 'settings-test')->first();
    $settings = GeneralSetting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();

    expect($settings)->not->toBeNull()
        ->and($settings->site_name)->toBe('Settings Test')
        ->and($settings->tax_rate)->toBe(15.0);
});
