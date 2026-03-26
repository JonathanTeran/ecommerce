<?php

namespace Database\Seeders;

use App\Models\GeneralSetting;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (GeneralSetting::count() === 0) {
            GeneralSetting::create([
                'site_name' => config('app.name'),
                'theme_color' => 'indigo',
                'default_language' => 'es',
            ]);
        }
    }
}
