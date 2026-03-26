<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);

        $user = User::firstOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'superadmin@amephia.com')],
            [
                'name' => 'Super Admin',
                'password' => bcrypt(env('SUPER_ADMIN_PASSWORD', \Illuminate\Support\Str::random(24))),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $user->assignRole('super_admin');
    }
}
