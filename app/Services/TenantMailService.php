<?php

namespace App\Services;

use App\Models\GeneralSetting;
use App\Models\Tenant;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;

class TenantMailService
{
    public function send(Mailable $mailable, ?Tenant $tenant = null): void
    {
        $tenant ??= app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            Mail::send($mailable);

            return;
        }

        $settings = GeneralSetting::where('tenant_id', $tenant->id)->first();

        if (! $settings || ! $settings->hasSmtpConfigured()) {
            Mail::send($mailable);

            return;
        }

        $mailer = $this->buildMailer($settings);
        $mailer->send($mailable);
    }

    private function buildMailer(GeneralSetting $settings): \Illuminate\Mail\Mailer
    {
        $encryption = $settings->smtp_encryption ?? 'tls';
        $scheme = match ($encryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => 'smtp',
        };

        $factory = new EsmtpTransportFactory;
        $transport = $factory->create(new Dsn(
            $scheme,
            $settings->smtp_host,
            $settings->smtp_username,
            $settings->smtp_password,
            $settings->smtp_port,
        ));

        $symfonyMailer = new \Symfony\Component\Mailer\Mailer($transport);

        $mailer = new \Illuminate\Mail\Mailer(
            'tenant',
            app('view'),
            $symfonyMailer,
            app('events'),
        );

        $mailer->alwaysFrom(
            $settings->mail_from_address,
            $settings->mail_from_name ?? $settings->site_name ?? config('app.name'),
        );

        return $mailer;
    }
}
