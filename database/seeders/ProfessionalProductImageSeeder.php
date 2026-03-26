<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProfessionalProductImageSeeder extends Seeder
{
    public function run()
    {
        // Map Product Name Part -> Image Filename
        $mapping = [
            'MacBook Pro' => 'macbook_pro_m3_max_real_1769099417837.png',
            'Dell XPS' => 'dell_xps_15_real_1769099432361.png',
            'ASUS ROG' => 'asus_rog_strix_real_1769099448608.png',
            'RTX 4090' => 'msi_rtx_4090_real_1769099497131.png',
            'Core i9' => 'intel_core_i9_14900k_real_1769099513756.png',
            'Reolink' => 'reolink_4k_kit_real_1769099531700.png',
            'Hikvision' => 'hikvision_colorvu_real_1769099548564.png',
        ];

        $sourceDir = '/Users/jonathanteran/.gemini/antigravity/brain/9d5b9a94-8269-40e7-b6ae-dcef221d7653';

        foreach ($mapping as $namePart => $filename) {
            $product = Product::where('name', 'like', "%$namePart%")->first();
            $path = $sourceDir.'/'.$filename;

            if ($product && File::exists($path)) {
                $this->command->info("Attaching image to {$product->name}...");

                // Clear existing placeholder images
                $product->clearMediaCollection('images');

                // Add new local image
                try {
                    $product->addMedia($path)
                        ->preservingOriginal()
                        ->toMediaCollection('images');
                    $this->command->info('Success!');
                } catch (\Exception $e) {
                    $this->command->error('Failed: '.$e->getMessage());
                }
            } else {
                $this->command->warn("Skipped: Product '$namePart' or file not found.");
            }
        }
    }
}
