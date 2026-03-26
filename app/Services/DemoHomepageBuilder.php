<?php

namespace App\Services;

use App\Enums\SectionType;
use App\Models\HomepageSection;
use App\Models\Tenant;

class DemoHomepageBuilder
{
    public function build(Tenant $tenant): void
    {
        $sections = $this->sections();

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
    }

    /** @return array<int, array{type: SectionType, name: string, config: array<string, mixed>}> */
    private function sections(): array
    {
        return [
            [
                'type' => SectionType::Hero,
                'name' => 'Hero Principal',
                'config' => [
                    'badge_text' => 'Tienda Demo',
                    'heading' => 'Bienvenido a Tu Tienda Online',
                    'subheading' => 'Descubre productos increibles con los mejores precios y envio rapido a todo el pais.',
                    'cta_text' => 'Explorar Tienda',
                    'cta_url' => '/shop',
                    'secondary_cta_text' => 'Ver Categorias',
                    'secondary_cta_url' => '/categories',
                    'background_image' => null,
                    'style' => [
                        'bg_type' => 'gradient',
                        'bg_color' => null,
                        'bg_gradient_from' => '#1e1b4b',
                        'bg_gradient_to' => '#312e81',
                        'bg_gradient_direction' => 'to-r',
                        'text_color' => '#ffffff',
                        'padding_preset' => 'extra',
                        'border_radius' => 'none',
                        'border_top' => false,
                        'border_bottom' => false,
                        'border_color' => null,
                        'font_family' => 'Poppins',
                    ],
                ],
            ],
            [
                'type' => SectionType::CategoryStrip,
                'name' => 'Categorias Rapidas',
                'config' => [
                    'categories' => [],
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#f8fafc',
                        'bg_gradient_from' => null,
                        'bg_gradient_to' => null,
                        'bg_gradient_direction' => 'to-b',
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
                    'heading' => 'Productos Destacados',
                    'source' => 'trending',
                    'category_id' => null,
                    'limit' => 8,
                    'enable_infinite_scroll' => true,
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#ffffff',
                        'bg_gradient_from' => null,
                        'bg_gradient_to' => null,
                        'bg_gradient_direction' => 'to-b',
                        'text_color' => '#111827',
                        'padding_preset' => 'spacious',
                        'border_radius' => 'none',
                        'border_top' => false,
                        'border_bottom' => false,
                        'border_color' => null,
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
                            'title' => 'Ofertas Especiales',
                            'subtitle' => 'Los mejores descuentos en productos seleccionados.',
                            'badge_text' => 'Hot Sale',
                            'badge_color' => 'red',
                            'button_text' => 'Ver Ofertas',
                            'button_url' => '/shop',
                            'image' => null,
                        ],
                        [
                            'title' => 'Nuevos Productos',
                            'subtitle' => 'Descubre las ultimas novedades de nuestro catalogo.',
                            'badge_text' => 'Nuevo',
                            'badge_color' => 'blue',
                            'button_text' => 'Ver Novedades',
                            'button_url' => '/shop',
                            'image' => null,
                        ],
                    ],
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#f1f5f9',
                        'bg_gradient_from' => null,
                        'bg_gradient_to' => null,
                        'bg_gradient_direction' => 'to-b',
                        'text_color' => '#1e293b',
                        'padding_preset' => 'normal',
                        'border_radius' => 'medium',
                        'border_top' => false,
                        'border_bottom' => false,
                        'border_color' => null,
                        'font_family' => 'Poppins',
                    ],
                ],
            ],
            [
                'type' => SectionType::CtaStrip,
                'name' => 'Banner CTA',
                'config' => [
                    'heading' => '¿Necesitas Ayuda?',
                    'description' => 'Nuestro equipo esta listo para asesorarte en tu compra.',
                    'button_text' => 'Contactanos',
                    'button_url' => '/contact',
                    'icon' => 'support',
                    'style' => [
                        'bg_type' => 'gradient',
                        'bg_color' => null,
                        'bg_gradient_from' => '#4f46e5',
                        'bg_gradient_to' => '#7c3aed',
                        'bg_gradient_direction' => 'to-r',
                        'text_color' => '#ffffff',
                        'padding_preset' => 'normal',
                        'border_radius' => 'none',
                        'border_top' => false,
                        'border_bottom' => false,
                        'border_color' => null,
                        'font_family' => null,
                    ],
                ],
            ],
            [
                'type' => SectionType::BrandSlider,
                'name' => 'Marcas Destacadas',
                'config' => [
                    'heading' => 'Marcas Destacadas',
                    'subheading' => 'Trabajamos con las mejores marcas del mercado',
                    'style' => [
                        'bg_type' => 'solid',
                        'bg_color' => '#ffffff',
                        'bg_gradient_from' => null,
                        'bg_gradient_to' => null,
                        'bg_gradient_direction' => 'to-b',
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
            [
                'type' => SectionType::ValueProps,
                'name' => 'Propuesta de Valor',
                'config' => [
                    'items' => [
                        ['icon' => 'truck', 'title' => 'Envio Rapido', 'description' => 'Entregas seguras a todo el pais en tiempo record.'],
                        ['icon' => 'check-circle', 'title' => 'Calidad Garantizada', 'description' => 'Todos nuestros productos pasan por control de calidad.'],
                        ['icon' => 'refresh', 'title' => 'Devoluciones Faciles', 'description' => '30 dias para cambios y devoluciones sin complicaciones.'],
                        ['icon' => 'support', 'title' => 'Soporte 24/7', 'description' => 'Atencion personalizada por chat, email o telefono.'],
                    ],
                    'style' => [
                        'bg_type' => 'gradient',
                        'bg_color' => null,
                        'bg_gradient_from' => '#18181b',
                        'bg_gradient_to' => '#27272a',
                        'bg_gradient_direction' => 'to-b',
                        'text_color' => '#ffffff',
                        'padding_preset' => 'spacious',
                        'border_radius' => 'none',
                        'border_top' => false,
                        'border_bottom' => false,
                        'border_color' => null,
                        'font_family' => 'Poppins',
                    ],
                ],
            ],
        ];
    }
}
