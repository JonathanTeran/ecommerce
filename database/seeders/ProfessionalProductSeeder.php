<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProfessionalProductSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure Brands Exist
        $brands = [
            'Apple' => 'apple',
            'Dell' => 'dell',
            'ASUS' => 'asus',
            'MSI' => 'msi',
            'Samsung' => 'samsung',
            'HP' => 'hp',
            'Lenovo' => 'lenovo',
            'Reolink' => 'reolink',
            'Hikvision' => 'hikvision',
            'Kingston' => 'kingston',
            'Logitech' => 'logitech',
        ];

        $brandModels = [];
        foreach ($brands as $name => $slug) {
            $brandModels[$name] = Brand::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_active' => true]
            );
        }

        // 2. Define Products by Category
        // Structure: Main Category -> [ Products ]
        $catalog = [
            'COMPUTADORAS' => [
                [
                    'name' => 'MacBook Pro 16" M3 Max Space Black',
                    'brand' => 'Apple',
                    'price' => 3499.00,
                    'compare_price' => 3699.00,
                    'short_description' => 'La laptop más potente de Apple con chip M3 Max, 48GB RAM y 1TB SSD.',
                    'description' => '<p>La <strong>MacBook Pro de 16 pulgadas</strong> da un paso gigante con los chips M3 Max, radicalmente más avanzados, que ofrecen un rendimiento y una eficiencia enormes para flujos de trabajo extremos.</p><ul><li>Chip M3 Max de 16 núcleos</li><li>GPU de 40 núcleos</li><li>48 GB de memoria unificada</li><li>1 TB de almacenamiento SSD</li><li>Pantalla Liquid Retina XDR de 16.2 pulgadas</li></ul>',
                    'specs' => ['Procesador' => 'M3 Max', 'RAM' => '48GB', 'Almacenamiento' => '1TB SSD', 'Pantalla' => '16.2" Liquid Retina XDR'],
                    'image' => 'https://placehold.co/800x600/1a1a1a/ffffff?text=MacBook+Pro+M3+Max',
                ],
                [
                    'name' => 'Dell XPS 15 OLED Touch 2024',
                    'brand' => 'Dell',
                    'price' => 2199.00,
                    'compare_price' => 2499.00,
                    'short_description' => 'Portátil premium con pantalla 3.5K OLED y procesador Intel Core i9.',
                    'description' => '<p>La <strong>Dell XPS 15</strong> es el equilibrio perfecto entre potencia y portabilidad. Diseñada con precisión CNC y fibra de carbono.</p><ul><li>Procesador Intel Core i9-13900H</li><li>NVIDIA GeForce RTX 4060</li><li>32 GB RAM DDR5</li><li>1 TB SSD NVMe</li><li>Pantalla OLED 3.5K Táctil InfinityEdge</li></ul>',
                    'specs' => ['Procesador' => 'Intel Core i9', 'RAM' => '32GB', 'GPU' => 'RTX 4060', 'Pantalla' => '15.6" OLED 3.5K'],
                    'image' => 'https://placehold.co/800x600/e1e1e1/333333?text=Dell+XPS+15',
                ],
                [
                    'name' => 'ASUS ROG Strix G16 Gaming Laptop',
                    'brand' => 'ASUS',
                    'price' => 1899.00,
                    'compare_price' => null,
                    'short_description' => 'Potencia gaming pura con Intel i9 y RTX 4070.',
                    'description' => '<p>Domina el juego con la <strong>ASUS ROG Strix G16</strong>. Equipada con el último hardware para streaming y juegos AAA.</p>',
                    'specs' => ['Procesador' => 'Intel Core i9-13980HX', 'RAM' => '16GB DDR5', 'GPU' => 'RTX 4070 8GB', 'Pantalla' => '16" FHD+ 165Hz'],
                    'image' => 'https://placehold.co/800x600/000000/ff0000?text=ASUS+ROG+Strix',
                ],
            ],
            'REPUESTOS PARA LAPTOP' => [
                [
                    'name' => 'Pantalla LED 15.6" Slim IPS 30 Pines',
                    'brand' => 'Samsung',
                    'price' => 85.00,
                    'compare_price' => 95.00,
                    'short_description' => 'Pantalla de repuesto original calidad A+ Full HD.',
                    'description' => '<p>Repuesto original compatible con HP, Dell, Lenovo, ASUS. Tecnología IPS para mejores ángulos de visión.</p><ul><li>Resolución: 1920x1080 (FHD)</li><li>Conector: 30 pines eDP</li><li>Acabado: Mate/Antireflejo</li></ul>',
                    'specs' => ['Tamaño' => '15.6"', 'Resolución' => 'FHD', 'Pines' => '30', 'Tipo' => 'Slim IPS'],
                    'image' => 'https://placehold.co/800x600/ffffff/333333?text=Pantalla+LED+15.6',
                ],
                [
                    'name' => 'Batería Original Dell Latitude 5480 68Wh',
                    'brand' => 'Dell',
                    'price' => 65.00,
                    'compare_price' => null,
                    'short_description' => 'Batería de larga duración 4 celdas, 68Wh.',
                    'description' => '<p>Batería genuina para laptops Dell Latitude serie 5000. Garantiza autonomía y seguridad.</p>',
                    'specs' => ['Voltaje' => '7.6V', 'Capacidad' => '68Wh', 'Celdas' => '4', 'Tipo' => 'Li-ion'],
                    'image' => 'https://placehold.co/800x600/ffffff/333333?text=Bateria+Dell+Original',
                ],
            ],
            'COMPONENTES PC' => [
                [
                    'name' => 'MSI GeForce RTX 4090 Gaming X Trio',
                    'brand' => 'MSI',
                    'price' => 1999.00,
                    'compare_price' => 2100.00,
                    'short_description' => 'La tarjeta gráfica definitiva. 24GB GDDR6X.',
                    'description' => '<p>La <strong>RTX 4090</strong> ofrece un salto enorme en rendimiento, eficiencia y gráficos impulsados por IA. Experimenta juegos con ray tracing ultra alto.</p>',
                    'specs' => ['VRAM' => '24GB GDDR6X', 'Cores' => '16384 CUDA', 'Boost Clock' => '2535 MHz', 'Bus' => '384-bit'],
                    'image' => 'https://placehold.co/800x600/101010/ff0000?text=MSI+RTX+4090',
                ],
                [
                    'name' => 'Procesador Intel Core i9-14900K',
                    'brand' => 'Hewlett-Packard', // Fallback or Generic
                    'price' => 599.00,
                    'compare_price' => 629.00,
                    'short_description' => '24 núcleos (8P + 16E) hasta 6.0 GHz.',
                    'description' => '<p>El procesador de escritorio más rápido del mundo. Ideal para gaming extremo y creación de contenido pesado.</p>',
                    'specs' => ['Núcleos' => '24', 'Hilos' => '32', 'Frecuencia Max' => '6.0 GHz', 'Socket' => 'LGA 1700'],
                    'image' => 'https://placehold.co/800x600/0071c5/ffffff?text=Intel+Core+i9',
                ],
            ],
            'VIDEO Y VIGILANCIA' => [
                [
                    'name' => 'Kit 4 Cámaras Reolink 4K PoE con NVR',
                    'brand' => 'Reolink',
                    'price' => 450.00,
                    'compare_price' => 520.00,
                    'short_description' => 'Sistema de seguridad completo con detección de personas y vehículos.',
                    'description' => '<p>Vigilancia inteligente 24/7 en ultra alta definición 4K. Instalación sencilla con un solo cable (PoE).</p><ul><li>4 Cámaras 8MP</li><li>NVR 2TB HDD</li><li>Visión nocturna mejorada</li><li>Acceso remoto vía App</li></ul>',
                    'specs' => ['Resolución' => '4K (8MP)', 'Canales' => '8', 'Almacenamiento' => '2TB', 'Conexión' => 'PoE'],
                    'image' => 'https://placehold.co/800x600/ffffff/000000?text=Kit+Reolink+4K',
                ],
                [
                    'name' => 'Cámara Hikvision ColorVu 5MP Audio',
                    'brand' => 'Hikvision',
                    'price' => 45.00,
                    'compare_price' => null,
                    'short_description' => 'Imágenes a color 24/7 incluso en oscuridad total.',
                    'description' => '<p>Tecnología ColorVu avanzada para seguridad nocturna superior. Micrófono incorporado.</p>',
                    'specs' => ['Resolución' => '5MP', 'Tecnología' => 'ColorVu', 'Audio' => 'Sí', 'Lente' => '2.8mm'],
                    'image' => 'https://placehold.co/800x600/ffffff/d7000f?text=Hikvision+ColorVu',
                ],
            ],
        ];

        foreach ($catalog as $categoryName => $products) {
            // Find main category (fuzzy search or exact)
            $category = Category::where('name', 'like', "%$categoryName%")->first();

            if (! $category) {
                $category = Category::create(['name' => $categoryName, 'is_active' => true, 'slug' => Str::slug($categoryName)]);
            }

            foreach ($products as $pData) {
                // Determine Brand
                $brandName = $pData['brand'];
                $brand = $brandModels[$brandName] ?? Brand::firstOrCreate(['name' => $brandName], ['slug' => Str::slug($brandName)]);

                $product = Product::updateOrCreate(
                    ['name' => $pData['name']],
                    [
                        'brand_id' => $brand->id,
                        'category_id' => $category->id,
                        'price' => $pData['price'],
                        'compare_price' => $pData['compare_price'],
                        'short_description' => $pData['short_description'],
                        'description' => $pData['description'],
                        'sku' => strtoupper(substr($brandName, 0, 3)).'-'.rand(1000, 9999),
                        'is_active' => true,
                        'is_featured' => $pData['price'] > 100, // Expensive items are featured
                        'is_new' => true,
                        'quantity' => rand(5, 50),
                        'specifications' => $pData['specs'], // Assuming cast to array works or json column
                        'meta_title' => $pData['name'],
                        'meta_description' => $pData['short_description'],
                    ]
                );

                // Attach Image via MediaLibrary if not already attached (avoid dupes on re-seed)
                // Note: Since we are using placehold.co external URLs, we can't easily "attach" them to media library
                // as local files without downloading.
                // FOR NOW: We will assume the frontend can handle an 'image_url' column OR we try to adding it as a URL if Spatie supports it (addMediaFromUrl).
                // Spatie supports addMediaFromUrl($url). Perfect.

                if ($product->getMedia('images')->isEmpty()) {
                    try {
                        $product->addMediaFromUrl($pData['image'])->toMediaCollection('images');
                    } catch (\Exception $e) {
                        // Fallback or ignore if offline
                    }
                }
            }
        }
    }
}
