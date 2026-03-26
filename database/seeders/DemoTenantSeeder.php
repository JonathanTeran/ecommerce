<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\TenantProvisioningService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'customer']);

        $slug = strtolower(str_replace(' ', '-', config('app.name')));

        if (Tenant::where('slug', $slug)->exists()) {
            return;
        }

        $plan = Plan::where('type', 'enterprise')->first();

        if (! $plan) {
            $this->command->warn('Plan Enterprise no encontrado. Ejecute PlanSeeder primero.');

            return;
        }

        $adminEmail = 'admin@' . $slug . '.com';

        app(TenantProvisioningService::class)->provision([
            'name' => config('app.name'),
            'slug' => $slug,
            'domain' => null,
            'theme_color' => 'indigo',
            'plan_id' => $plan->id,
            'admin_name' => 'Admin',
            'admin_email' => $adminEmail,
            'admin_password' => 'password',
        ]);

        $this->command->info("Tenant demo \"{$slug}\" creado con {$adminEmail} / password");
    }
}
