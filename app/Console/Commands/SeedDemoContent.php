<?php

namespace App\Console\Commands;

use App\Enums\SectionType;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Console\Command;

class SeedDemoContent extends Command
{
    protected $signature = 'app:seed-demo-content';

    protected $description = 'Seed demo homepage sections and product images for the default tenant';

    public function handle(): int
    {
        $tenant = Tenant::where('is_default', true)->first();

        if (! $tenant) {
            $this->error('No default tenant found.');

            return self::FAILURE;
        }

        app()->instance('current_tenant', $tenant);

        $this->seedHomepage($tenant);
        $this->seedProductImages($tenant);

        $this->info('Demo content seeded successfully!');

        return self::SUCCESS;
    }

    private function seedHomepage(Tenant $tenant): void
    {
        $this->info('Creating homepage sections...');

        // Delete existing sections for this tenant
        HomepageSection::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->delete();

        $sections = [
            [
                'type' => SectionType::Hero,
                'name' => 'Hero Principal',
                'config' => [
                    'badge_text' => '🔥 Ofertas de Temporada',
                    'heading' => 'Tecnología al Mejor Precio',
                    'subheading' => 'Laptops, componentes, periféricos y más. Envío a todo el país con garantía incluida.',
                    'cta_text' => 'Ver Productos',
                    'cta_url' => '/shop',
                    'secondary_cta_text' => 'Ver Categorías',
                    'secondary_cta_url' => '/categories',
                    'background_image' => null,
                    'style' => [
                        'bg_type' => 'gradient',
                        'bg_gradient_from' => '#0f172a',
                        'bg_gradient_to' => '#1e3a5f',
                        'bg_gradient_direction' => 'to-br',
                        'text_color' => '#ffffff',
                        'padding_preset' => 'extra',
                        'border_radius' => 'none',
                        'border_top' => false,
                        'border_bottom' => false,
                        'font_family' => 'Poppins',
                    ],
                ],
            ],
            [
                'type' => SectionType::ValueProps,
                'name' => 'Propuesta de Valor',
                'config' => [
                    'items' => [
                        ['icon' => 'truck', 'title' => 'Envío Gratis', 'description' => 'En compras mayores a $100 con cobertura nacional.'],
                        ['icon' => 'shield', 'title' => 'Garantía Oficial', 'description' => 'Todos nuestros productos con garantía de 6 a 12 meses.'],
                        ['icon' => 'refresh', 'title' => 'Devoluciones Fáciles', 'description' => '30 días para cambios y devoluciones sin complicaciones.'],
                        ['icon' => 'support', 'title' => 'Soporte Técnico', 'description' => 'Asesoría especializada por WhatsApp, email o teléfono.'],
                    ],
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#f8fafc',
                        'text_color' => '#1e293b',
                        'padding_preset' => 'normal',
                        'border_radius' => 'none',
                        'border_top' => false,
                        'border_bottom' => true,
                        'border_color' => '#e2e8f0',
                        'font_family' => 'Poppins',
                    ],
                ],
            ],
            [
                'type' => SectionType::CategoryStrip,
                'name' => 'Categorías Populares',
                'config' => [
                    'categories' => [],
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#ffffff',
                        'text_color' => '#1e293b',
                        'padding_preset' => 'normal',
                        'border_radius' => 'none',
                        'border_top' => false,
                        'border_bottom' => true,
                        'border_color' => '#e2e8f0',
                        'font_family' => 'Poppins',
                    ],
                ],
            ],
            [
                'type' => SectionType::ProductGrid,
                'name' => 'Productos Destacados',
                'config' => [
                    'heading' => '⭐ Productos Destacados',
                    'source' => 'featured',
                    'category_id' => null,
                    'limit' => 8,
                    'enable_infinite_scroll' => false,
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#ffffff',
                        'text_color' => '#111827',
                        'padding_preset' => 'spacious',
                        'border_radius' => 'none',
                        'border_top' => false,
                        'border_bottom' => false,
                        'font_family' => 'Poppins',
                    ],
                ],
            ],
            [
                'type' => SectionType::PromoBanners,
                'name' => 'Banners Promocionales',
                'config' => [
                    'banners' => [
                        [
                            'title' => 'Laptops desde $449',
                            'subtitle' => 'Las mejores marcas: ASUS, HP, Dell, Lenovo y Apple con financiamiento disponible.',
                            'badge_text' => 'Hot Sale',
                            'badge_color' => 'red',
                            'button_text' => 'Ver Laptops',
                            'button_url' => '/shop',
                            'image' => null,
                        ],
                        [
                            'title' => 'Arma tu PC Gaming',
                            'subtitle' => 'Procesadores Intel y AMD, tarjetas RTX 4060 y memorias Corsair a precios increíbles.',
                            'badge_text' => 'Nuevo',
                            'badge_color' => 'blue',
                            'button_text' => 'Ver Componentes',
                            'button_url' => '/shop',
                            'image' => null,
                        ],
                    ],
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#f1f5f9',
                        'text_color' => '#1e293b',
                        'padding_preset' => 'normal',
                        'border_radius' => 'medium',
                        'border_top' => false,
                        'border_bottom' => false,
                        'font_family' => 'Poppins',
                    ],
                ],
            ],
            [
                'type' => SectionType::ProductGrid,
                'name' => 'Nuevos Productos',
                'config' => [
                    'heading' => '🆕 Recién Llegados',
                    'source' => 'new',
                    'category_id' => null,
                    'limit' => 4,
                    'enable_infinite_scroll' => false,
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#ffffff',
                        'text_color' => '#111827',
                        'padding_preset' => 'normal',
                        'border_radius' => 'none',
                        'border_top' => false,
                        'border_bottom' => false,
                        'font_family' => 'Poppins',
                    ],
                ],
            ],
            [
                'type' => SectionType::CtaStrip,
                'name' => 'CTA Soporte',
                'config' => [
                    'heading' => '¿Necesitas asesoría técnica?',
                    'description' => 'Nuestro equipo de expertos te ayuda a elegir el equipo perfecto para tu necesidad.',
                    'button_text' => 'Contáctanos por WhatsApp',
                    'button_url' => '/about',
                    'icon' => 'support',
                    'style' => [
                        'bg_type' => 'gradient',
                        'bg_gradient_from' => '#4f46e5',
                        'bg_gradient_to' => '#7c3aed',
                        'bg_gradient_direction' => 'to-r',
                        'text_color' => '#ffffff',
                        'padding_preset' => 'normal',
                        'border_radius' => 'none',
                        'border_top' => false,
                        'border_bottom' => false,
                    ],
                ],
            ],
            [
                'type' => SectionType::BrandSlider,
                'name' => 'Marcas Aliadas',
                'config' => [
                    'heading' => 'Marcas que Distribuimos',
                    'subheading' => 'Distribuidores autorizados de las marcas líderes del mercado tecnológico',
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#ffffff',
                        'text_color' => '#374151',
                        'padding_preset' => 'normal',
                        'border_radius' => 'none',
                        'border_top' => true,
                        'border_bottom' => true,
                        'border_color' => '#e5e7eb',
                        'font_family' => 'Poppins',
                    ],
                ],
            ],
        ];

        foreach ($sections as $index => $section) {
            $model = new HomepageSection([
                'type' => $section['type'],
                'name' => $section['name'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'config' => $section['config'],
            ]);
            $model->tenant_id = $tenant->id;
            $model->save();
        }

        $this->info(count($sections) . ' homepage sections created.');
    }

    private function seedProductImages(Tenant $tenant): void
    {
        $this->info('Adding product images...');

        $products = Product::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereDoesntHave('media')
            ->get();

        if ($products->isEmpty()) {
            $this->info('All products already have images.');

            return;
        }

        // Map product keywords to Picsum image IDs for consistent, relevant images
        $imageMap = [
            'laptop' => [0, 1, 2, 3, 4],
            'ssd' => [5, 6],
            'disco' => [7, 8],
            'micro' => [9],
            'pen drive' => [10],
            'memoria ram' => [11, 12],
            'mouse' => [13],
            'teclado' => [14],
            'audífono' => [15],
            'webcam' => [16],
            'router' => [17],
            'switch' => [18],
            'impresora' => [19, 20],
            'cámara' => [21, 22],
            'kit' => [23],
            'procesador' => [24, 25],
            'tarjeta' => [26],
            'fuente' => [27],
            'cargador' => [28, 29, 30],
            'batería' => [31],
            'ups' => [32],
        ];

        // Use Picsum photos for realistic placeholder images
        $picsumIds = [
            180, 201, 60, 119, 160,    // laptops/tech
            250, 256,                    // storage
            276, 282,                    // external drives
            292, 295,                    // micro sd, pen drive
            306, 312,                    // ram
            316, 319,                    // mouse, keyboard
            325, 329,                    // audio, webcam
            335, 338,                    // networking
            342, 348,                    // printers
            355, 360, 366,              // cameras/security
            372, 376,                    // processors
            380, 384,                    // gpu, psu
            388, 392, 396,              // chargers
            400, 404,                    // battery, ups
        ];

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products->values() as $index => $product) {
            $picsumId = $picsumIds[$index % count($picsumIds)];
            $imageUrl = "https://picsum.photos/id/{$picsumId}/800/800";

            try {
                $product
                    ->addMediaFromUrl($imageUrl)
                    ->toMediaCollection('images');
            } catch (\Exception $e) {
                // Fallback to random image if specific ID fails
                try {
                    $product
                        ->addMediaFromUrl("https://picsum.photos/800/800?random={$index}")
                        ->toMediaCollection('images');
                } catch (\Exception $e2) {
                    $this->warn(" Failed for {$product->name}: {$e2->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Images added to {$products->count()} products.");
    }
}
