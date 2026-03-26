<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSpecificationsSeeder extends Seeder
{
    public function run()
    {
        $products = [
            'MacBook Pro' => [
                'Procesador' => 'Apple M3 Max (14 núcleos CPU, 30 núcleos GPU)',
                'Memoria RAM' => '36 GB Unificada',
                'Almacenamiento' => '1 TB SSD',
                'Pantalla' => '16.2" Liquid Retina XDR (3456 x 2234)',
                'Sistema Operativo' => 'macOS Sonoma',
                'Color' => 'Negro Espacial',
                'Batería' => 'Hasta 22 horas',
            ],
            'Dell XPS' => [
                'Procesador' => 'Intel Core i9-13900H (14 núcleos)',
                'Memoria RAM' => '32 GB DDR5 4800MHz',
                'Almacenamiento' => '1 TB NVMe SSD Gen4',
                'Pantalla' => '15.6" OLED 3.5K (3456 x 2160) Táctil',
                'Gráficos' => 'NVIDIA GeForce RTX 4060 8GB',
                'Sistema Operativo' => 'Windows 11 Pro',
                'Material' => 'Aluminio mecanizado y fibra de carbono',
            ],
            'ASUS ROG' => [
                'Procesador' => 'Intel Core i9-13980HX 24 núcleos',
                'Gráficos' => 'NVIDIA GeForce RTX 4080 12GB GDDR6',
                'Pantalla' => '16" ROG Nebula HDR QHD+ 240Hz',
                'Memoria RAM' => '32 GB DDR5 4800MHz',
                'Almacenamiento' => '1 TB PCIe 4.0 NVMe M.2 SSD',
                'Teclado' => 'RGB por tecla con Aura Sync',
                'Refrigeración' => 'ROG Intelligent Cooling con metal líquido',
            ],
            'RTX 4090' => [
                'Memoria de Video' => '24 GB GDDR6X',
                'Núcleos CUDA' => '16384',
                'Reloj Boost' => '2610 MHz (Modo Gaming y Silencioso)',
                'Interfaz de Memoria' => '384-bit',
                'Bus Estándar' => 'PCI Express Gen 4',
                'Conectores de Energía' => '1 x 16-pin',
                'Dimensiones' => '337 x 140 x 77 mm',
            ],
            'Core i9' => [
                'Núcleos Totales' => '24 (8 Performance + 16 Efficient)',
                'Hilos Totales' => '32',
                'Frecuencia Turbo Máx' => '6.0 GHz',
                'Caché' => '36 MB Intel Smart Cache',
                'Zócalo' => 'LGA 1700',
                'Gráficos Integrados' => 'Intel UHD Graphics 770',
                'TDP Base/Turbo' => '125W / 253W',
            ],
            'Reolink' => [
                'Resolución' => '4K Ultra HD (8MP)',
                'Visión Nocturna' => 'Hasta 30 metros',
                'Almacenamiento NVR' => '2 TB HDD preinstalado (Soporta hasta 12TB)',
                'Canales' => '8 Canales PoE',
                'Audio' => 'Grabación de audio (micrófono integrado)',
                'Resistencia' => 'IP66 Impermeable (Exterior/Interior)',
                'Acceso Remoto' => 'App Reolink gratuita (iOS/Android)',
            ],
            'Hikvision' => [
                'Resolución' => '5 Megapíxeles (2960 × 1665)',
                'Tecnología' => 'ColorVu (Color 24/7)',
                'Lente' => 'Fijo de 2.8 mm',
                'Iluminación' => 'Luz blanca hasta 20m',
                'Audio' => 'Micrófono incorporado (Audio sobre Coax)',
                'Protección' => 'IP67 (Exterior)',
                'Material' => 'Cuerpo principal de metal',
            ],
        ];

        foreach ($products as $namePart => $specs) {
            $product = Product::where('name', 'like', "%$namePart%")->first();

            if ($product) {
                // Use replaceTranslations to overwrite the entire array for this locale,
                // avoiding any merge issues with existing corrupted data.
                $product->replaceTranslations('specifications', ['es' => $specs]);
                $product->save();

                $this->command->info("Updated specs for: {$product->name}");
            } else {
                $this->command->warn("Product not found matching: $namePart");
            }
        }
    }
}
