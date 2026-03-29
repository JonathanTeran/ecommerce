<?php

namespace App\Enums;

enum ThemeTemplate: string
{
    case Default = 'default';
    case Fashion = 'fashion';
    case Tech = 'tech';
    case Minimal = 'minimal';

    public function label(): string
    {
        return match ($this) {
            self::Default => 'Clásico',
            self::Fashion => 'Moda & Lifestyle',
            self::Tech => 'Tecnología & Gaming',
            self::Minimal => 'Minimalista & Elegante',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Default => 'Diseño equilibrado para cualquier tipo de tienda. Fondo oscuro con acentos coloridos.',
            self::Fashion => 'Diseño cálido con tipografía serif, ideado para ropa, accesorios y relojes.',
            self::Tech => 'Diseño moderno con detalles neón, perfecto para electrónica, componentes y gaming.',
            self::Minimal => 'Diseño limpio de fondo blanco, tipografía sans-serif. Ideal para productos premium.',
        };
    }

    public function previewImage(): string
    {
        return match ($this) {
            self::Default => '/images/themes/default-preview.jpg',
            self::Fashion => '/images/themes/fashion-preview.jpg',
            self::Tech => '/images/themes/tech-preview.jpg',
            self::Minimal => '/images/themes/minimal-preview.jpg',
        };
    }

    /**
     * The blade component prefix for section variants.
     * e.g. "sections.fashion.hero" instead of "sections.hero"
     */
    public function sectionPrefix(): string
    {
        return match ($this) {
            self::Default => 'sections',
            self::Fashion => 'sections.themes.fashion',
            self::Tech => 'sections.themes.tech',
            self::Minimal => 'sections.themes.minimal',
        };
    }

    /**
     * Navbar style override for this theme.
     */
    public function navbarStyle(): string
    {
        return match ($this) {
            self::Default => 'solid_dark',
            self::Fashion => 'solid_white',
            self::Tech => 'solid_dark',
            self::Minimal => 'solid_white',
        };
    }

    /**
     * Default CSS variables for this theme.
     */
    public function cssVariables(): array
    {
        return match ($this) {
            self::Default => [
                '--theme-bg' => '#0f172a',
                '--theme-surface' => '#1e293b',
                '--theme-text' => '#f8fafc',
                '--theme-accent' => '#6366f1',
            ],
            self::Fashion => [
                '--theme-bg' => '#faf9f6',
                '--theme-surface' => '#ffffff',
                '--theme-text' => '#1a1a1a',
                '--theme-accent' => '#b8860b',
            ],
            self::Tech => [
                '--theme-bg' => '#0a0a0a',
                '--theme-surface' => '#141414',
                '--theme-text' => '#e5e5e5',
                '--theme-accent' => '#00ff88',
            ],
            self::Minimal => [
                '--theme-bg' => '#ffffff',
                '--theme-surface' => '#f9fafb',
                '--theme-text' => '#111827',
                '--theme-accent' => '#111827',
            ],
        };
    }

    public function colorSwatches(): array
    {
        return array_values($this->cssVariables());
    }
}
