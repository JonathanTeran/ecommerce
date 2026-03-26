<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CompleteProductDataSeeder extends Seeder
{
    public function run()
    {
        // 1. SPECIFICATIONS FOR PARTS
        $partsSpecs = [
            'ASUS 65W' => [
                'Voltaje de Entrada' => '100-240V ~ 50-60Hz',
                'Salida' => '19V - 3.42A',
                'Potencia' => '65W',
                'Conector' => 'USB Tipo C',
                'Compatibilidad' => 'Universal USB-C ASUS',
                'Garantía' => '12 Meses',
            ],
            'ASUS 150W' => [
                'Voltaje de Entrada' => '100-240V',
                'Salida' => '20V - 7.5A',
                'Potencia' => '150W',
                'Conector' => '4.5 * 3.0 mm (Punta Azul)',
                'Garantía' => '12 Meses',
            ],
            'ASUS 240W' => [
                'Voltaje de Entrada' => '100-240V',
                'Salida' => '20V - 12A',
                'Potencia' => '240W',
                'Conector' => '6.0 * 3.7 mm',
                'Serie' => 'ROG Gaming',
                'Garantía' => '12 Meses',
            ],
            'ACER 135W' => [
                'Voltaje de Entrada' => '100-240V',
                'Salida' => '19V - 7.1A',
                'Potencia' => '135W',
                'Conector' => '5.5 * 1.7 mm (Punta Naranja)',
                'Garantía' => '12 Meses',
            ],
            'ACER 180W' => [
                'Voltaje de Entrada' => '100-240V',
                'Salida' => '19.5V - 9.23A',
                'Potencia' => '180W',
                'Conector' => '5.5 * 1.7 mm',
                'Serie' => 'Predator / Nitro',
                'Garantía' => '12 Meses',
            ],
            'Pantalla LED' => [
                'Tamaño' => '15.6 Pulgadas',
                'Resolución' => '1920 x 1080 (FHD)',
                'Tecnología' => 'IPS',
                'Conector' => '30 Pines',
                'Superficie' => 'Mate / Antireflejo',
                'Tipo' => 'Slim',
                'Garantía' => '6 Meses',
            ],
            'Batería Original Dell' => [
                'Modelo' => 'GJKNX / 93FTF',
                'Capacidad' => '68 Wh',
                'Voltaje' => '7.6V',
                'Celdas' => '4 Celdas',
                'Tecnología' => 'Ion de Litio',
                'Compatibilidad' => 'Latitude 5480, 5580, Precision 3520',
                'Garantía' => '12 Meses',
            ],
        ];

        // 2. IMAGES FOR PARTS (Mapping name part -> filename)
        // Using available images. For battery, using Dell laptop image as fallback due to API limit.
        $images = [
            'ASUS' => 'laptop_charger_generic_real_1769101898955.png',
            'ACER' => 'laptop_charger_generic_real_1769101898955.png',
            'Pantalla' => 'laptop_screen_generic_real_1769101919106.png',
            'Batería' => 'dell_xps_15_real_1769099432361.png', // Fallback
        ];

        $sourceDir = '/Users/jonathanteran/.gemini/antigravity/brain/9d5b9a94-8269-40e7-b6ae-dcef221d7653';

        // Apply Specs
        foreach ($partsSpecs as $namePart => $specs) {
            $product = Product::where('name', 'like', "%$namePart%")->first();
            if ($product) {
                $product->replaceTranslations('specifications', ['es' => $specs]);
                $product->save();
                $this->command->info("Specs populated for: {$product->name}");
            }
        }

        // Apply Images
        foreach ($images as $namePart => $filename) {
            // Find products matching the name part that DON'T have images yet
            $products = Product::where('name', 'like', "%$namePart%")
                ->get()
                ->filter(function ($p) {
                    return $p->getMedia('images')->isEmpty();
                });

            foreach ($products as $product) {
                if (File::exists($sourceDir.'/'.$filename)) {
                    $product->addMedia($sourceDir.'/'.$filename)
                        ->preservingOriginal()
                        ->toMediaCollection('images');
                    $this->command->info("Image attached to: {$product->name}");
                }
            }
        }
    }
}
