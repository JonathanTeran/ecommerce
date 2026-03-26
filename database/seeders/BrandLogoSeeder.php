<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandLogoSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            'ASUS' => 'https://upload.wikimedia.org/wikipedia/commons/2/2e/ASUS_Logo.svg',
            'ACER' => 'https://upload.wikimedia.org/wikipedia/commons/0/00/Acer_2011.svg',
            'HP' => 'https://upload.wikimedia.org/wikipedia/commons/a/ad/HP_logo_2012.svg',
            'DELL' => 'https://upload.wikimedia.org/wikipedia/commons/4/48/Dell_Logo.svg',
            'LENOVO' => 'https://upload.wikimedia.org/wikipedia/commons/b/b8/Lenovo_logo_2015.svg',
            'SAMSUNG' => 'https://cdn.worldvectorlogo.com/logos/samsung.svg',
            'KINGSTON' => 'https://cdn.worldvectorlogo.com/logos/kingston-technology-logo.svg',
            'LOGITECH' => 'https://upload.wikimedia.org/wikipedia/commons/1/17/Logitech_logo.svg',
        ];

        foreach ($brands as $name => $url) {
            $brand = Brand::where('name', $name)->first();

            if ($brand) {
                $this->command->info("Updating logo for: {$name}");
                try {
                    $brand->clearMediaCollection('logo');
                    $brand->addMediaFromUrl($url)
                        ->toMediaCollection('logo');
                } catch (\Exception $e) {
                    $this->command->error("Failed to update logo for {$name}: ".$e->getMessage());
                }
            } else {
                $this->command->warn("Brand not found: {$name}");
            }
        }
    }
}
