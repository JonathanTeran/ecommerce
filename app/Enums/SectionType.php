<?php

namespace App\Enums;

use App\Support\SectionStyleHelper;

enum SectionType: string
{
    case Hero = 'hero';
    case CategoryStrip = 'category_strip';
    case PromoBanners = 'promo_banners';
    case CtaStrip = 'cta_strip';
    case ProductGrid = 'product_grid';
    case BrandSlider = 'brand_slider';
    case ValueProps = 'value_props';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero / Banner Principal',
            self::CategoryStrip => 'Categorias Rapidas',
            self::PromoBanners => 'Banners Promocionales',
            self::CtaStrip => 'Banner CTA',
            self::ProductGrid => 'Grilla de Productos',
            self::BrandSlider => 'Slider de Marcas',
            self::ValueProps => 'Propuesta de Valor',
        };
    }

    public function bladeComponent(): string
    {
        return 'sections.' . str_replace('_', '-', $this->value);
    }

    /** @return array<string, mixed> */
    public function defaultConfig(): array
    {
        return match ($this) {
            self::Hero => [
                'badge_text' => 'Tecnología de Punta',
                'heading' => 'Potencia tu Vida Digital',
                'subheading' => 'Encuentra las mejores laptops, componentes y accesorios para llevar tu rendimiento al siguiente nivel.',
                'cta_text' => 'Ver Catálogo',
                'cta_url' => '/shop',
                'secondary_cta_text' => 'Explorar Categorías',
                'secondary_cta_url' => '/categories',
                'background_image' => null,
                'style' => SectionStyleHelper::defaultStyle(),
            ],
            self::CategoryStrip => [
                'categories' => [],
                'style' => SectionStyleHelper::defaultStyle(),
            ],
            self::PromoBanners => [
                'banners' => [
                    [
                        'title' => 'Construye tu PC Ideal',
                        'subtitle' => 'Tarjetas de video, procesadores y cases de alto rendimiento.',
                        'badge_text' => 'Zona Gamer',
                        'badge_color' => 'red',
                        'button_text' => 'Ver Productos',
                        'button_url' => '/shop?tag=gamer',
                        'image' => null,
                    ],
                    [
                        'title' => 'Oficina en Casa',
                        'subtitle' => 'Laptops, All-in-One e impresoras para tu trabajo.',
                        'badge_text' => 'Productividad',
                        'badge_color' => 'blue',
                        'button_text' => 'Ver Ofertas',
                        'button_url' => '/shop?category=laptops',
                        'image' => null,
                    ],
                ],
                'style' => SectionStyleHelper::defaultStyle(),
            ],
            self::CtaStrip => [
                'heading' => '¿Repuestos para tu Laptop?',
                'description' => 'Baterías, Pantallas, Teclados y Cargadores para todos los modelos.',
                'button_text' => 'Buscar Repuesto',
                'button_url' => '/shop?category=repuestos-para-laptop',
                'icon' => 'puzzle',
                'style' => SectionStyleHelper::defaultStyle(),
            ],
            self::ProductGrid => [
                'heading' => 'Tendencias',
                'source' => 'trending',
                'category_id' => null,
                'limit' => 8,
                'enable_infinite_scroll' => true,
                'style' => SectionStyleHelper::defaultStyle(),
            ],
            self::BrandSlider => [
                'heading' => 'Marcas con las que Trabajamos',
                'subheading' => 'Las mejores marcas de tecnología en un solo lugar',
                'style' => SectionStyleHelper::defaultStyle(),
            ],
            self::ValueProps => [
                'items' => [
                    ['icon' => 'truck', 'title' => 'Envío Gratis', 'description' => 'En pedidos superiores a $150. Envíos garantizados.'],
                    ['icon' => 'check-circle', 'title' => 'Garantía Asegurada', 'description' => 'Todos nuestros productos cuentan con garantía de fábrica.'],
                    ['icon' => 'refresh', 'title' => 'Devoluciones Fáciles', 'description' => 'Política de devolución de 30 días para su tranquilidad.'],
                    ['icon' => 'support', 'title' => 'Soporte Técnico', 'description' => 'Asesoría personalizada para armar tu PC.'],
                ],
                'style' => SectionStyleHelper::defaultStyle(),
            ],
        };
    }
}
