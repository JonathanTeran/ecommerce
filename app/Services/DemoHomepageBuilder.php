<?php

namespace App\Services;

use App\Enums\SectionType;
use App\Models\HomepageSection;
use App\Models\Tenant;

class DemoHomepageBuilder
{
    /**
     * Build homepage sections for a tenant, optionally styled with a color scheme.
     *
     * @param  array{primary?: string, secondary?: string, background?: string, text?: string, accent?: string}|null  $colorScheme
     */
    public function build(Tenant $tenant, ?array $colorScheme = null): void
    {
        $sections = $this->sections();

        foreach ($sections as $index => $section) {
            // Override section styles if a color scheme is provided
            if ($colorScheme && isset($section['config']['style'])) {
                $section['config']['style'] = $this->applyColorScheme($section['config']['style'], $colorScheme);
            }

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

    /**
     * @param  array<string, mixed>  $style
     * @param  array{primary?: string, secondary?: string, background?: string, text?: string, accent?: string}  $colors
     * @return array<string, mixed>
     */
    private function applyColorScheme(array $style, array $colors): array
    {
        $bg = $colors['background'] ?? '#ffffff';
        $text = $colors['text'] ?? '#333333';
        $primary = $colors['primary'] ?? '#4f46e5';
        $secondary = $colors['secondary'] ?? $primary;
        $isLight = $this->isLightColor($bg);

        $style['bg_type'] = 'gradient';
        $style['bg_gradient_from'] = $isLight ? $bg : $primary;
        $style['bg_gradient_to'] = $isLight ? '#f9fafb' : $secondary;
        $style['text_color'] = $isLight ? $text : '#ffffff';

        return $style;
    }

    private function isLightColor(string $hex): bool
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return ($r * 299 + $g * 587 + $b * 114) / 1000 > 128;
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
            [
                'type' => SectionType::Testimonials,
                'name' => 'Testimonios',
                'config' => [
                    'heading' => 'Lo que dicen nuestros clientes',
                    'subheading' => 'Miles de clientes satisfechos respaldan nuestra calidad',
                    'reviews' => [
                        ['name' => 'Carlos M.', 'subtitle' => 'Compra verificada', 'rating' => 5, 'text' => 'Excelente calidad y entrega rapida. Definitivamente volvere a comprar.'],
                        ['name' => 'Maria F.', 'subtitle' => 'Cliente frecuente', 'rating' => 5, 'text' => 'Excelente atencion al cliente. Me asesoraron y el producto llego perfecto.'],
                        ['name' => 'Jose R.', 'subtitle' => 'Compra verificada', 'rating' => 4, 'text' => 'Buenos precios y la garantia me da tranquilidad.'],
                        ['name' => 'Ana T.', 'subtitle' => 'Compra verificada', 'rating' => 5, 'text' => 'Calidad original y buen precio. Muy recomendado.'],
                        ['name' => 'Luis P.', 'subtitle' => 'Cliente desde 2024', 'rating' => 5, 'text' => 'Gran compra. La instalacion fue sencilla y todo funciona perfecto.'],
                        ['name' => 'Gabriela M.', 'subtitle' => 'Compra verificada', 'rating' => 4, 'text' => 'Envio rapido y bien empacado. Muy satisfecha con la compra.'],
                    ],
                    'style' => [
                        'bg_type' => 'gradient',
                        'bg_color' => null,
                        'bg_gradient_from' => '#1e293b',
                        'bg_gradient_to' => '#0f172a',
                        'bg_gradient_direction' => 'to-br',
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
            [
                'type' => SectionType::SocialProof,
                'name' => 'Prueba Social',
                'config' => [
                    'heading' => 'La confianza de miles nos respalda',
                    'stats' => [
                        ['value' => '2,500+', 'label' => 'Clientes Satisfechos', 'icon' => 'users'],
                        ['value' => '5,000+', 'label' => 'Pedidos Entregados', 'icon' => 'package'],
                        ['value' => '4.8/5', 'label' => 'Calificacion Promedio', 'icon' => 'star'],
                        ['value' => '24/7', 'label' => 'Soporte Tecnico', 'icon' => 'headset'],
                    ],
                    'style' => [
                        'bg_type' => 'gradient',
                        'bg_color' => null,
                        'bg_gradient_from' => '#312e81',
                        'bg_gradient_to' => '#1e1b4b',
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
            [
                'type' => SectionType::NewsletterBanner,
                'name' => 'Newsletter',
                'config' => [
                    'heading' => 'Recibe ofertas exclusivas',
                    'subheading' => 'Suscribete y obtén un 10% de descuento en tu primera compra',
                    'button_text' => 'Suscribirme',
                    'placeholder' => 'Tu correo electronico',
                    'style' => [
                        'bg_type' => 'gradient',
                        'bg_color' => null,
                        'bg_gradient_from' => '#4f46e5',
                        'bg_gradient_to' => '#7c3aed',
                        'bg_gradient_direction' => 'to-r',
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
