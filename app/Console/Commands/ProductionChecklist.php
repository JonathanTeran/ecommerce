<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProductionChecklist extends Command
{
    protected $signature = 'app:production-checklist';

    protected $description = 'Verify production-readiness of the application';

    public function handle(): int
    {
        $this->info('=== Production Readiness Checklist ===');
        $this->newLine();

        $issues = 0;

        // APP_DEBUG
        if (config('app.debug')) {
            $this->error('[FAIL] APP_DEBUG is true — must be false in production');
            $issues++;
        } else {
            $this->info('[OK] APP_DEBUG is false');
        }

        // APP_ENV
        if (config('app.env') !== 'production') {
            $this->warn('[WARN] APP_ENV is "' . config('app.env') . '" — should be "production"');
            $issues++;
        } else {
            $this->info('[OK] APP_ENV is production');
        }

        // APP_KEY
        if (empty(config('app.key'))) {
            $this->error('[FAIL] APP_KEY is not set');
            $issues++;
        } else {
            $this->info('[OK] APP_KEY is set');
        }

        // SESSION_ENCRYPT
        if (! config('session.encrypt')) {
            $this->warn('[WARN] SESSION_ENCRYPT is false — recommended true for production');
            $issues++;
        } else {
            $this->info('[OK] SESSION_ENCRYPT is true');
        }

        // MAIL_MAILER
        if (config('mail.default') === 'log') {
            $this->warn('[WARN] MAIL_MAILER is "log" — emails will not be sent');
            $issues++;
        } else {
            $this->info('[OK] MAIL_MAILER is "' . config('mail.default') . '"');
        }

        // DB connection
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $this->info('[OK] Database connection established');
        } catch (\Exception $e) {
            $this->error('[FAIL] Cannot connect to database: ' . $e->getMessage());
            $issues++;
        }

        // Queue driver
        if (config('queue.default') === 'sync') {
            $this->warn('[WARN] QUEUE_CONNECTION is "sync" — jobs will run synchronously');
            $issues++;
        } else {
            $this->info('[OK] QUEUE_CONNECTION is "' . config('queue.default') . '"');
        }

        // Payment gateways (per-tenant, not global)
        $this->info('[INFO] Payment gateways (Nuvei, PayPhone) are configured per tenant in Admin > Ajustes del Sistema > Pasarelas de Pago');

        // Check how many tenants have gateways configured
        $tenantsWithGateways = \App\Models\GeneralSetting::withoutGlobalScopes()
            ->whereNotNull('payment_gateways_config')
            ->count();
        $this->info("[INFO] {$tenantsWithGateways} tenant(s) have payment gateway config");

        // Super admin env vars
        if (empty(env('SUPER_ADMIN_PASSWORD'))) {
            $this->warn('[WARN] SUPER_ADMIN_PASSWORD is not set — seeder will generate random password');
        } else {
            $this->info('[OK] SUPER_ADMIN_PASSWORD is set');
        }

        // Local disk serve
        if (config('filesystems.disks.local.serve')) {
            $this->error('[FAIL] Local disk has serve=true — private files may be accessible');
            $issues++;
        } else {
            $this->info('[OK] Local disk serve is disabled');
        }

        $this->newLine();

        if ($issues === 0) {
            $this->info('All checks passed! Application is ready for production.');

            return self::SUCCESS;
        }

        $this->warn("Found {$issues} issue(s) to resolve before production deployment.");

        return self::FAILURE;
    }
}
