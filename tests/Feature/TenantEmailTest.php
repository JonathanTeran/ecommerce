<?php

use App\Mail\OrderConfirmationMail;
use App\Mail\QuotationApprovedMail;
use App\Mail\QuotationReceivedMail;
use App\Mail\QuotationRejectedMail;
use App\Mail\WelcomeUserMail;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function createTenantWithSmtp(array $smtpOverrides = []): Tenant
{
    $tenant = Tenant::create([
        'name' => 'SMTP Tenant',
        'slug' => 'smtp-tenant-' . uniqid(),
    ]);

    GeneralSetting::withoutGlobalScopes()->create(array_merge([
        'tenant_id' => $tenant->id,
        'site_name' => 'Test Store',
        'mail_from_name' => 'Test Store',
        'mail_from_address' => 'store@example.com',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_username' => 'user@example.com',
        'smtp_password' => 'secret',
        'smtp_encryption' => 'tls',
    ], $smtpOverrides));

    return $tenant;
}

function createTenantWithoutSmtp(): Tenant
{
    $tenant = Tenant::create([
        'name' => 'No SMTP Tenant',
        'slug' => 'no-smtp-tenant-' . uniqid(),
    ]);

    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'site_name' => 'Basic Store',
    ]);

    return $tenant;
}

it('falls back to default mailer when no tenant is set', function () {
    Mail::fake();

    $user = User::factory()->create();
    $mailable = new WelcomeUserMail($user);

    $service = new TenantMailService;
    $service->send($mailable);

    Mail::assertQueued(WelcomeUserMail::class);
});

it('falls back to default mailer when tenant has no SMTP configured', function () {
    Mail::fake();

    $tenant = createTenantWithoutSmtp();
    app()->instance('current_tenant', $tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $mailable = new WelcomeUserMail($user);

    $service = new TenantMailService;
    $service->send($mailable);

    Mail::assertQueued(WelcomeUserMail::class);
});

it('detects SMTP configuration correctly', function () {
    $tenant = createTenantWithSmtp();
    $settings = GeneralSetting::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->first();

    expect($settings->hasSmtpConfigured())->toBeTrue();
});

it('detects missing SMTP configuration', function () {
    $tenant = createTenantWithoutSmtp();
    $settings = GeneralSetting::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->first();

    expect($settings->hasSmtpConfigured())->toBeFalse();
});

it('requires host, port and from address for SMTP to be configured', function () {
    $tenant = createTenantWithSmtp(['smtp_host' => null]);
    $settings = GeneralSetting::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->first();

    expect($settings->hasSmtpConfigured())->toBeFalse();
});

it('encrypts smtp_password in database', function () {
    $tenant = createTenantWithSmtp(['smtp_password' => 'my-secret-pass']);

    $raw = \DB::table('general_settings')
        ->where('tenant_id', $tenant->id)
        ->value('smtp_password');

    expect($raw)->not->toBe('my-secret-pass')
        ->and($raw)->not->toBeNull();

    $settings = GeneralSetting::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->first();

    expect($settings->smtp_password)->toBe('my-secret-pass');
});

it('resolves tenant from container automatically', function () {
    Mail::fake();

    $tenant = createTenantWithoutSmtp();
    app()->instance('current_tenant', $tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $service = new TenantMailService;
    $service->send(new WelcomeUserMail($user));

    Mail::assertQueued(WelcomeUserMail::class);
});

it('uses explicit tenant over container tenant', function () {
    Mail::fake();

    $tenantA = createTenantWithoutSmtp();
    $tenantB = createTenantWithoutSmtp();
    app()->instance('current_tenant', $tenantA);

    $user = User::factory()->create(['tenant_id' => $tenantB->id]);

    $service = new TenantMailService;
    $service->send(new WelcomeUserMail($user), $tenantB);

    Mail::assertQueued(WelcomeUserMail::class);
});

it('builds OrderConfirmationMail correctly', function () {
    $tenant = Tenant::create(['name' => 'T', 'slug' => 'mail-test-' . uniqid()]);

    GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'site_name' => 'My Store',
    ]);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $order = Order::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'order_number' => 'ORD-001',
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'placed_at' => now(),
    ]);

    $mailable = new OrderConfirmationMail($order);

    $mailable->assertHasTo($user->email);
    $mailable->assertHasSubject('Confirmacion de Pedido #ORD-001');
});

it('builds QuotationReceivedMail correctly', function () {
    $user = User::factory()->create();

    $quotation = Quotation::withoutGlobalScopes()->create([
        'user_id' => $user->id,
        'quotation_number' => 'COT-TEST-0001',
        'status' => 'pending',
        'customer_name' => 'Juan',
        'customer_email' => 'juan@test.com',
        'subtotal' => 200,
        'tax_amount' => 30,
        'total' => 230,
    ]);

    $mailable = new QuotationReceivedMail($quotation);

    $mailable->assertHasTo('juan@test.com');
    $mailable->assertHasSubject('Cotización Recibida #COT-TEST-0001');
});

it('builds QuotationApprovedMail correctly', function () {
    $user = User::factory()->create();

    $quotation = Quotation::withoutGlobalScopes()->create([
        'user_id' => $user->id,
        'quotation_number' => 'COT-TEST-0002',
        'status' => 'approved',
        'customer_name' => 'Maria',
        'customer_email' => 'maria@test.com',
        'subtotal' => 300,
        'tax_amount' => 45,
        'total' => 345,
    ]);

    $mailable = new QuotationApprovedMail($quotation);

    $mailable->assertHasTo('maria@test.com');
    $mailable->assertHasSubject('Cotización Aprobada #COT-TEST-0002');
});

it('builds QuotationRejectedMail correctly', function () {
    $user = User::factory()->create();

    $quotation = Quotation::withoutGlobalScopes()->create([
        'user_id' => $user->id,
        'quotation_number' => 'COT-TEST-0003',
        'status' => 'rejected',
        'customer_name' => 'Pedro',
        'customer_email' => 'pedro@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'rejection_reason' => 'No stock',
    ]);

    $mailable = new QuotationRejectedMail($quotation);

    $mailable->assertHasTo('pedro@test.com');
    $mailable->assertHasSubject('Cotización Rechazada #COT-TEST-0003');
});

it('builds WelcomeUserMail correctly', function () {
    $user = User::factory()->create(['email' => 'nuevo@test.com']);

    $mailable = new WelcomeUserMail($user);

    $mailable->assertHasTo('nuevo@test.com');
    $mailable->assertHasSubject('Bienvenido a nuestra tienda');
});

it('SMTP settings page saves configuration', function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

    $tenant = Tenant::create(['name' => 'Admin Tenant', 'slug' => 'admin-smtp-' . uniqid()]);

    $settings = GeneralSetting::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'site_name' => 'Admin Store',
        'theme_color' => 'indigo',
        'default_language' => 'es',
    ]);

    app()->instance('current_tenant', $tenant);

    $settings->update([
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => 'admin@gmail.com',
        'smtp_password' => 'app-password',
        'smtp_encryption' => 'tls',
        'mail_from_name' => 'Admin Store',
        'mail_from_address' => 'admin@store.com',
    ]);

    $settings->refresh();

    expect($settings->smtp_host)->toBe('smtp.gmail.com')
        ->and($settings->smtp_port)->toBe(587)
        ->and($settings->smtp_username)->toBe('admin@gmail.com')
        ->and($settings->smtp_password)->toBe('app-password')
        ->and($settings->smtp_encryption)->toBe('tls')
        ->and($settings->mail_from_name)->toBe('Admin Store')
        ->and($settings->mail_from_address)->toBe('admin@store.com')
        ->and($settings->hasSmtpConfigured())->toBeTrue();
});
