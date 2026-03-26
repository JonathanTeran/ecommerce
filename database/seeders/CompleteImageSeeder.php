<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CompleteImageSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBrandLogos();
        $this->seedProductImages();
    }

    private function seedBrandLogos(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('  SEEDING BRAND LOGOS');
        $this->command->info('═══════════════════════════════════════════');

        $sourceDir = database_path('seeders/images/brands');

        // Map brand name => image filename pattern
        $brandImages = [
            'Apple' => 'brand_apple_',
            'Samsung' => 'brand_samsung_',
            'Dell' => 'brand_dell_',
            'HP' => 'brand_hp_',
            'Lenovo' => 'brand_lenovo_',
            'ASUS' => 'brand_asus_',
            'Acer' => 'brand_acer_',
            'Logitech' => 'brand_logitech_',
            'Razer' => 'brand_razer_',
            'Corsair' => 'brand_corsair_',
            'NVIDIA' => 'brand_nvidia_',
            'AMD' => 'brand_amd_',
            'Intel' => 'brand_intel_',
            'Kingston' => 'brand_kingston_',
            'Western Digital' => 'brand_western_digital_',
            'TP-Link' => 'brand_tplink_',
            'Epson' => 'brand_epson_',
        ];

        // Also map for case-insensitive brand matching
        $fallbackUrls = [
            'Canon' => 'https://images.unsplash.com/photo-1622979135225-d2ba269cf1ac?w=400&h=400&fit=crop',
            'Microsoft' => 'https://images.unsplash.com/photo-1633419461186-7d40a38105ec?w=400&h=400&fit=crop',
            'Sony' => 'https://images.unsplash.com/photo-1511268559489-34b624fbfcf5?w=400&h=400&fit=crop',
            'HyperX' => 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=400&h=400&fit=crop',
            'MSI' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?w=400&h=400&fit=crop',
        ];

        foreach ($brandImages as $brandName => $filePattern) {
            $brand = Brand::where('name', $brandName)->whereDoesntHave('media')->first();

            if (! $brand) {
                $this->command->line("  ⏭ {$brandName} - already has logo or not found");

                continue;
            }

            // Find the image file
            $files = glob("{$sourceDir}/{$filePattern}*.png");

            if (empty($files)) {
                $this->command->warn("  ⚠ {$brandName} - image file not found");

                continue;
            }

            try {
                $brand->clearMediaCollection('logo');
                $brand->addMedia($files[0])
                    ->preservingOriginal()
                    ->toMediaCollection('logo');
                $this->command->info("  ✅ {$brandName} - logo attached");
            } catch (\Exception $e) {
                $this->command->error("  ❌ {$brandName} - failed: {$e->getMessage()}");
            }
        }

        // Handle brands without local images via fallback URLs
        foreach ($fallbackUrls as $brandName => $url) {
            $brand = Brand::where('name', $brandName)->whereDoesntHave('media')->first();

            if (! $brand) {
                $this->command->line("  ⏭ {$brandName} - already has logo or not found");

                continue;
            }

            try {
                $brand->clearMediaCollection('logo');
                $brand->addMediaFromUrl($url)
                    ->toMediaCollection('logo');
                $this->command->info("  ✅ {$brandName} - logo attached (from URL)");
            } catch (\Exception $e) {
                $this->command->error("  ❌ {$brandName} - failed: {$e->getMessage()}");
            }
        }
    }

    private function seedProductImages(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('  SEEDING PRODUCT IMAGES');
        $this->command->info('═══════════════════════════════════════════');

        // Product Name Part => Unsplash/Pexels URL for representative product images
        $productImages = [
            // Laptop Chargers
            'ASUS 65W TIPO C' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&h=800&fit=crop',
            'ASUS 150W 19V' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&h=800&fit=crop',
            'ASUS 240W 20V' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&h=800&fit=crop',
            'ACER 135W 19V' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&h=800&fit=crop',
            'ACER 180W 19V' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&h=800&fit=crop',

            // Laptops
            'MacBook Pro' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&h=800&fit=crop',
            'Dell XPS' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=800&h=800&fit=crop',
            'ASUS ROG' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&h=800&fit=crop',
            'ThinkPad' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&h=800&fit=crop',
            'HP Pavilion' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&h=800&fit=crop',
            'Chromebook' => 'https://images.unsplash.com/photo-1531297484001-80022131f5a1?w=800&h=800&fit=crop',

            // Smartphones
            'iPhone 16' => 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=800&h=800&fit=crop',
            'Galaxy S24' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&h=800&fit=crop',
            'Galaxy A55' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800&h=800&fit=crop',

            // PC Components
            'RTX 4090' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?w=800&h=800&fit=crop',
            'Ryzen 9' => 'https://images.unsplash.com/photo-1555617981-dac3880eac6e?w=800&h=800&fit=crop',
            'Core i7' => 'https://images.unsplash.com/photo-1555617981-dac3880eac6e?w=800&h=800&fit=crop',
            'Vengeance DDR5' => 'https://images.unsplash.com/photo-1562976540-1502c2145186?w=800&h=800&fit=crop',
            '990 Pro' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=800&h=800&fit=crop',
            'FURY Beast' => 'https://images.unsplash.com/photo-1562976540-1502c2145186?w=800&h=800&fit=crop',
            'RM850x' => 'https://images.unsplash.com/photo-1587202372634-32705e3bf49c?w=800&h=800&fit=crop',

            // Peripherals
            'MX Master' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800&h=800&fit=crop',
            'DeathAdder' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=800&h=800&fit=crop',
            'MX Keys' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&h=800&fit=crop',
            'K70 RGB' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=800&h=800&fit=crop',
            'UltraSharp' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=800&h=800&fit=crop',
            'Odyssey' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=800&h=800&fit=crop',
            'Cloud III' => 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=800&h=800&fit=crop',
            'C920s' => 'https://images.unsplash.com/photo-1616587226157-48e49175ee20?w=800&h=800&fit=crop',

            // Networking & Printing
            'Archer AX73' => 'https://images.unsplash.com/photo-1606904825846-647eb07f5be2?w=800&h=800&fit=crop',
            'EcoTank' => 'https://images.unsplash.com/photo-1612815154858-60aa4c59eaa6?w=800&h=800&fit=crop',

            // Gaming, Software & Accessories
            'PlayStation 5' => 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=800&h=800&fit=crop',
            'Silla Gaming' => 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?w=800&h=800&fit=crop',
            'Microsoft 365' => 'https://images.unsplash.com/photo-1633419461186-7d40a38105ec?w=800&h=800&fit=crop',
            'Windows 11' => 'https://images.unsplash.com/photo-1633419461186-7d40a38105ec?w=800&h=800&fit=crop',
            'AirPods Pro' => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=800&h=800&fit=crop',
        ];

        foreach ($productImages as $namePart => $url) {
            $product = Product::where('name', 'like', "%{$namePart}%")
                ->whereDoesntHave('media')
                ->first();

            if (! $product) {
                $this->command->line("  ⏭ {$namePart} - already has image or not found");

                continue;
            }

            try {
                $product->clearMediaCollection('images');
                $product->addMediaFromUrl($url)
                    ->toMediaCollection('images');
                $this->command->info("  ✅ {$product->name} - image attached");
            } catch (\Exception $e) {
                $this->command->error("  ❌ {$product->name} - failed: {$e->getMessage()}");
            }
        }
    }
}
