<?php

namespace App\Services;

use App\Models\GeneralSetting;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TenantProvisioningService
{
    /**
     * Provision a complete tenant with all dependencies.
     *
     * @param  array{
     *     name: string,
     *     slug?: string,
     *     domain?: string|null,
     *     theme_color?: string,
     *     country?: string,
     *     currency?: string,
     *     language?: string,
     *     plan_id: int,
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password: string,
     *     trial_ends_at?: string|null,
     *     is_demo?: bool,
     *     seed_demo_data?: bool,
     * }  $data
     */
    public function provision(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            Role::firstOrCreate(['name' => 'admin']);

            $country = $data['country'] ?? 'EC';
            $countryDefaults = $this->getCountryDefaults($country);

            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $data['slug'] ?? Str::slug($data['name']),
                'domain' => $data['domain'] ?? null,
                'theme_color' => $data['theme_color'] ?? 'indigo',
                'country' => $country,
                'currency' => $data['currency'] ?? $countryDefaults['currency'],
                'language' => $data['language'] ?? $countryDefaults['language'],
                'timezone' => $countryDefaults['timezone'],
                'is_active' => true,
                'is_demo' => $data['is_demo'] ?? false,
                'trial_ends_at' => $data['trial_ends_at'] ?? now()->addDays(14),
            ]);

            GeneralSetting::create([
                'tenant_id' => $tenant->id,
                'site_name' => $tenant->name,
                'tax_rate' => $countryDefaults['tax_rate'],
            ]);

            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $data['plan_id'],
                'status' => 'active',
                'starts_at' => now(),
            ]);

            $admin = User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);

            $admin->forceFill(['email_verified_at' => now()])->save();
            $admin->assignRole('admin');

            // Always build homepage sections for new tenants
            app(DemoHomepageBuilder::class)->build($tenant);

            // Seed demo catalog data (categories, products, brands)
            if ($data['seed_demo_data'] ?? true) {
                app(TenantDemoDataBuilder::class)->build($tenant);
            }

            return $tenant;
        });
    }

    /**
     * @return array{currency: string, language: string, timezone: string, tax_rate: float}
     */
    private function getCountryDefaults(string $country): array
    {
        return match (strtoupper($country)) {
            'EC' => ['currency' => 'USD', 'language' => 'es', 'timezone' => 'America/Guayaquil', 'tax_rate' => 15],
            'CO' => ['currency' => 'COP', 'language' => 'es', 'timezone' => 'America/Bogota', 'tax_rate' => 19],
            'MX' => ['currency' => 'MXN', 'language' => 'es', 'timezone' => 'America/Mexico_City', 'tax_rate' => 16],
            'PE' => ['currency' => 'PEN', 'language' => 'es', 'timezone' => 'America/Lima', 'tax_rate' => 18],
            'CL' => ['currency' => 'CLP', 'language' => 'es', 'timezone' => 'America/Santiago', 'tax_rate' => 19],
            'US' => ['currency' => 'USD', 'language' => 'en', 'timezone' => 'America/New_York', 'tax_rate' => 0],
            'ES' => ['currency' => 'EUR', 'language' => 'es', 'timezone' => 'Europe/Madrid', 'tax_rate' => 21],
            default => ['currency' => 'USD', 'language' => 'es', 'timezone' => 'America/Guayaquil', 'tax_rate' => 15],
        };
    }
}
