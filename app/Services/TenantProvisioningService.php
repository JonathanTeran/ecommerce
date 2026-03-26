<?php

namespace App\Services;

use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\DemoHomepageBuilder;
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
     *     plan_id: int,
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password: string,
     *     trial_ends_at?: string|null,
     *     is_demo?: bool,
     * }  $data
     */
    public function provision(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            Role::firstOrCreate(['name' => 'admin']);

            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $data['slug'] ?? Str::slug($data['name']),
                'domain' => $data['domain'] ?? null,
                'theme_color' => $data['theme_color'] ?? 'indigo',
                'is_active' => true,
                'is_demo' => $data['is_demo'] ?? false,
                'trial_ends_at' => $data['trial_ends_at'] ?? null,
            ]);

            GeneralSetting::create([
                'tenant_id' => $tenant->id,
                'site_name' => $tenant->name,
                'tax_rate' => 15,
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

            if ($data['is_demo'] ?? false) {
                app(DemoHomepageBuilder::class)->build($tenant);
            }

            return $tenant;
        });
    }
}
