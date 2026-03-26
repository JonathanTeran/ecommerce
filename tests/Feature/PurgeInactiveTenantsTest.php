<?php

use App\Models\Tenant;
use App\Models\User;

use function Pest\Laravel\artisan;

it('purges tenants deactivated for more than 3 months', function () {
    $oldTenant = Tenant::factory()->deactivated(4)->create();
    $activeTenant = Tenant::factory()->create();

    artisan('tenants:purge-inactive')->assertSuccessful();

    expect(Tenant::withTrashed()->find($oldTenant->id))->toBeNull();
    expect(Tenant::find($activeTenant->id))->not->toBeNull();
});

it('does not purge tenants deactivated less than 3 months ago', function () {
    $recentTenant = Tenant::factory()->deactivated(2)->create();

    artisan('tenants:purge-inactive')->assertSuccessful();

    expect(Tenant::find($recentTenant->id))->not->toBeNull();
});

it('supports dry run mode', function () {
    $tenant = Tenant::factory()->deactivated(4)->create();

    artisan('tenants:purge-inactive', ['--dry-run' => true])->assertSuccessful();

    expect(Tenant::withTrashed()->find($tenant->id))->not->toBeNull();
});

it('purges related data when purging a tenant', function () {
    $tenant = Tenant::factory()->deactivated(4)->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    artisan('tenants:purge-inactive')->assertSuccessful();

    expect(User::withTrashed()->find($user->id))->toBeNull();
});

it('allows custom months threshold', function () {
    $tenant = Tenant::factory()->deactivated(2)->create();

    artisan('tenants:purge-inactive', ['--months' => 1])->assertSuccessful();

    expect(Tenant::withTrashed()->find($tenant->id))->toBeNull();
});

it('can deactivate and reactivate a tenant', function () {
    $tenant = Tenant::factory()->create();

    $tenant->deactivate();

    expect($tenant->fresh())
        ->is_active->toBeFalse()
        ->deactivated_at->not->toBeNull();

    $tenant->reactivate();

    expect($tenant->fresh())
        ->is_active->toBeTrue()
        ->deactivated_at->toBeNull();
});

it('identifies tenants pending purge', function () {
    $tenant = Tenant::factory()->deactivated(4)->create();

    expect($tenant->isPendingPurge())->toBeTrue();
});

it('does not mark recently deactivated tenants as pending purge', function () {
    $tenant = Tenant::factory()->deactivated(1)->create();

    expect($tenant->isPendingPurge())->toBeFalse();
});
