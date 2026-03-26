<?php

use App\Enums\Module;
use App\Livewire\Auth\Modals;
use App\Mail\AccountApprovedMail;
use App\Mail\AccountPendingApprovalMail;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'approval-store']);
    app()->instance('current_tenant', $this->tenant);

    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise-approval', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);

    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
});

// ═══════════════════════════════════════════
// Registration with approval required
// ═══════════════════════════════════════════

it('creates inactive user when approval is required', function () {
    Mail::fake();

    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
        'require_account_approval' => true,
    ]);

    Livewire::test(Modals::class)
        ->set('mode', 'register')
        ->set('name', 'Pending User')
        ->set('registerEmail', 'pending@test.com')
        ->set('registerPassword', 'password123')
        ->set('registerPasswordConfirmation', 'password123')
        ->set('acceptLegalTerms', true)
        ->call('register')
        ->assertSet('mode', 'pending-approval');

    $user = User::where('email', 'pending@test.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->is_active)->toBeFalse()
        ->and($user->tenant_id)->toBe($this->tenant->id);

    Mail::assertQueued(AccountPendingApprovalMail::class, function ($mail) {
        return $mail->user->email === 'pending@test.com';
    });
});

it('creates active user when approval is not required', function () {
    Mail::fake();

    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Open Store',
        'require_account_approval' => false,
    ]);

    Livewire::test(Modals::class)
        ->set('mode', 'register')
        ->set('name', 'Active User')
        ->set('registerEmail', 'active@test.com')
        ->set('registerPassword', 'password123')
        ->set('registerPasswordConfirmation', 'password123')
        ->set('acceptLegalTerms', true)
        ->call('register');

    $user = User::where('email', 'active@test.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->is_active)->toBeTrue();

    Mail::assertNotQueued(AccountPendingApprovalMail::class);
});

it('notifies admin when user registers with approval required', function () {
    Notification::fake();
    Mail::fake();

    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');

    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
        'require_account_approval' => true,
    ]);

    Livewire::test(Modals::class)
        ->set('mode', 'register')
        ->set('name', 'Notify Test')
        ->set('registerEmail', 'notify@test.com')
        ->set('registerPassword', 'password123')
        ->set('registerPasswordConfirmation', 'password123')
        ->set('acceptLegalTerms', true)
        ->call('register');

    Notification::assertSentTo($admin, NewUserRegistered::class);
});

// ═══════════════════════════════════════════
// Login blocked for inactive users
// ═══════════════════════════════════════════

it('blocks login for inactive user', function () {
    $user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => false,
        'password' => bcrypt('password123'),
    ]);

    Livewire::test(Modals::class)
        ->set('mode', 'login')
        ->set('email', $user->email)
        ->set('password', 'password123')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
});

it('allows login for active user', function () {
    $user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'password' => bcrypt('password123'),
    ]);

    Livewire::test(Modals::class)
        ->set('mode', 'login')
        ->set('email', $user->email)
        ->set('password', 'password123')
        ->call('login')
        ->assertHasNoErrors();

    $this->assertAuthenticatedAs($user);
});

// ═══════════════════════════════════════════
// Admin approval sends email
// ═══════════════════════════════════════════

it('sends approval email when admin activates user', function () {
    Mail::fake();

    $inactiveUser = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => false,
    ]);

    $inactiveUser->update(['is_active' => true]);
    app(\App\Services\TenantMailService::class)->send(new AccountApprovedMail($inactiveUser));

    Mail::assertQueued(AccountApprovedMail::class, function ($mail) use ($inactiveUser) {
        return $mail->user->id === $inactiveUser->id;
    });
});

// ═══════════════════════════════════════════
// Tenant Isolation
// ═══════════════════════════════════════════

it('approval setting is per tenant', function () {
    $tenantA = $this->tenant;
    GeneralSetting::create([
        'tenant_id' => $tenantA->id,
        'site_name' => 'Store A',
        'require_account_approval' => true,
    ]);

    $tenantB = Tenant::factory()->create();
    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenantB->id,
        'site_name' => 'Store B',
        'require_account_approval' => false,
    ]);

    // Tenant A requires approval
    app()->instance('current_tenant', $tenantA);
    expect(GeneralSetting::requiresAccountApproval())->toBeTrue();

    // Tenant B does not require approval
    app()->instance('current_tenant', $tenantB);
    cache()->flush();
    expect(GeneralSetting::requiresAccountApproval())->toBeFalse();
});

it('default is no approval required when setting does not exist', function () {
    expect(GeneralSetting::requiresAccountApproval())->toBeFalse();
});

// ═══════════════════════════════════════════
// Email rendering
// ═══════════════════════════════════════════

it('account approved email renders correctly', function () {
    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
    ]);

    $user = User::factory()->create([
        'name' => 'Juan Aprobado',
        'tenant_id' => $this->tenant->id,
    ]);

    $mailable = new AccountApprovedMail($user);
    $rendered = $mailable->render();

    expect($rendered)
        ->toContain('Juan Aprobado')
        ->toContain('aprobada');
});

it('account pending approval email renders correctly', function () {
    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
    ]);

    $user = User::factory()->create([
        'name' => 'Maria Pendiente',
        'tenant_id' => $this->tenant->id,
    ]);

    $mailable = new AccountPendingApprovalMail($user);
    $rendered = $mailable->render();

    expect($rendered)
        ->toContain('Maria Pendiente')
        ->toContain('Pendiente de Aprobación');
});
