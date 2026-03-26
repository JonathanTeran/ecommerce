<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BannerSeeder extends Seeder
{
    public function run()
    {
        // Ensure one main banner exists
        $banners = [
            [
                'position' => 'home_slider_1',
                'title' => 'Nueva Colección 2026',
                'subtitle' => 'Descubre la tecnología del futuro hoy.',
                'image' => '/Users/jonathanteran/.gemini/antigravity/brain/9d5b9a94-8269-40e7-b6ae-dcef221d7653/new_collection_2026_banner_1769099987744.png',
                'sort' => 1,
            ],
            [
                'position' => 'home_slider_2',
                'title' => 'Componentes de Alta Gama',
                'subtitle' => 'Potencia tu PC con lo último en hardware.',
                'image' => '/Users/jonathanteran/.gemini/antigravity/brain/9d5b9a94-8269-40e7-b6ae-dcef221d7653/pc_components_banner_2026_1769100148752.png',
                'sort' => 2,
            ],
        ];

        foreach ($banners as $data) {
            $banner = Banner::firstOrNew(['position' => $data['position']]);

            $banner->fill([
                'title' => $data['title'],
                'subtitle' => $data['subtitle'],
                'button_text' => 'Ver Productos',
                'button_url' => '/shop',
                'is_active' => true,
                'sort_order' => $data['sort'],
            ])->save();

            if (File::exists($data['image'])) {
                $banner->clearMediaCollection('image');
                $banner->addMedia($data['image'])
                    ->preservingOriginal()
                    ->toMediaCollection('image');
                $this->command->info("Banner {$data['position']} updated successfully.");
            } else {
                $this->command->warn('Image not found: '.$data['image']);
            }
        }
    }
}
