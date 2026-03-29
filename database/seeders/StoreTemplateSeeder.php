<?php

namespace Database\Seeders;

use App\Models\StoreTemplate;
use Illuminate\Database\Seeder;

class StoreTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Anton',
                'slug' => 'anton',
                'description' => 'Plantilla moderna y elegante para tiendas de moda, ropa y accesorios. Tipografia Poppins, animaciones suaves, mega menu con imagenes y sliders de productos destacados.',
                'category' => 'fashion',
                'preview_image' => 'templates/previews/anton-preview.jpg',
                'assets_path' => 'templates/anton',
                'css_file' => 'templates/anton/css/style.css',
                'color_scheme' => [
                    'primary' => '#000000',
                    'secondary' => '#c09853',
                    'background' => '#ffffff',
                    'text' => '#333333',
                    'accent' => '#c09853',
                ],
                'fonts' => [
                    'heading' => 'Poppins',
                    'body' => 'Poppins',
                ],
                'features' => [
                    'Mega menu con imagenes',
                    'Slider de hero con multiples slides',
                    'Grilla de productos 3/4/5 columnas',
                    'Lookbook / Catalogo visual',
                    'Blog integrado',
                ],
                'is_active' => true,
                'is_premium' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Jovenca',
                'slug' => 'jovenca',
                'description' => 'Plantilla premium para joyerias, relojerias y tiendas de accesorios de lujo. Diseno sofisticado con animaciones elegantes, sidebar de busqueda y galeria de productos con zoom.',
                'category' => 'jewelry',
                'preview_image' => 'templates/previews/jovenca-preview.jpg',
                'assets_path' => 'templates/jovenca',
                'css_file' => 'templates/jovenca/css/style.css',
                'color_scheme' => [
                    'primary' => '#1a1a1a',
                    'secondary' => '#c8a165',
                    'background' => '#ffffff',
                    'text' => '#333333',
                    'accent' => '#c8a165',
                ],
                'fonts' => [
                    'heading' => 'Playfair Display',
                    'body' => 'DM Sans',
                ],
                'features' => [
                    'Diseno premium para productos de lujo',
                    'Galeria con zoom de producto',
                    'Barra superior con info de contacto',
                    'Sidebar de busqueda offcanvas',
                    'Pagina de FAQ integrada',
                ],
                'is_active' => true,
                'is_premium' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Radios',
                'slug' => 'radios',
                'description' => 'Plantilla completa para tiendas de electronica, tecnologia y gadgets. Incluye mega menu por categorias, comparador de productos, barra de flash deals con countdown y seccion de marcas.',
                'category' => 'electronics',
                'preview_image' => 'templates/previews/radios-preview.jpg',
                'assets_path' => 'templates/radios',
                'css_file' => 'templates/radios/css/main.css',
                'color_scheme' => [
                    'primary' => '#ff6600',
                    'secondary' => '#1a1a2e',
                    'background' => '#ffffff',
                    'text' => '#333333',
                    'accent' => '#ff6600',
                ],
                'fonts' => [
                    'heading' => 'Rubik',
                    'body' => 'Rubik',
                ],
                'features' => [
                    'Mega menu por departamentos',
                    'Flash deals con countdown timer',
                    'Comparador de productos',
                    'Sidebar de filtros avanzados',
                    'Seccion de marcas destacadas',
                ],
                'is_active' => true,
                'is_premium' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($templates as $template) {
            StoreTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
