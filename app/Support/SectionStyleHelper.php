<?php

namespace App\Support;

class SectionStyleHelper
{
    private const PADDING_MAP = [
        'compact' => '1rem',
        'normal' => '3rem',
        'spacious' => '5rem',
        'extra' => '8rem',
    ];

    private const RADIUS_MAP = [
        'none' => null,
        'small' => '0.5rem',
        'medium' => '1rem',
        'large' => '1.5rem',
        'full' => '3rem',
    ];

    private const GRADIENT_DIRECTIONS = [
        'to-b' => 'to bottom',
        'to-r' => 'to right',
        'to-br' => 'to bottom right',
        'to-bl' => 'to bottom left',
        'to-t' => 'to top',
        'to-tr' => 'to top right',
    ];

    /** @var array<string, string> */
    public const FONT_OPTIONS = [
        'Inter' => 'Inter',
        'Poppins' => 'Poppins',
        'Montserrat' => 'Montserrat',
        'Playfair Display' => 'Playfair Display',
        'Raleway' => 'Raleway',
        'Roboto' => 'Roboto',
    ];

    public static function toInlineCss(array $style): string
    {
        if (empty($style)) {
            return '';
        }

        $css = [];

        // Background
        if (($style['bg_type'] ?? 'solid') === 'gradient'
            && ($style['bg_gradient_from'] ?? null)
            && ($style['bg_gradient_to'] ?? null)) {
            $dir = self::GRADIENT_DIRECTIONS[$style['bg_gradient_direction'] ?? 'to-b'] ?? 'to bottom';
            $css[] = "background: linear-gradient({$dir}, {$style['bg_gradient_from']}, {$style['bg_gradient_to']})";
        } elseif ($style['bg_color'] ?? null) {
            $css[] = "background-color: {$style['bg_color']}";
        }

        // Text color
        if ($style['text_color'] ?? null) {
            $css[] = "color: {$style['text_color']}";
        }

        // Padding
        $preset = $style['padding_preset'] ?? null;
        if ($preset && isset(self::PADDING_MAP[$preset])) {
            $value = self::PADDING_MAP[$preset];
            $css[] = "padding-top: {$value}";
            $css[] = "padding-bottom: {$value}";
        }

        // Border radius
        $radius = $style['border_radius'] ?? 'none';
        if ($radius !== 'none' && isset(self::RADIUS_MAP[$radius])) {
            $css[] = 'border-radius: ' . self::RADIUS_MAP[$radius];
        }

        // Borders
        $borderColor = $style['border_color'] ?? '#e5e7eb';
        if ($style['border_top'] ?? false) {
            $css[] = "border-top: 1px solid {$borderColor}";
        }
        if ($style['border_bottom'] ?? false) {
            $css[] = "border-bottom: 1px solid {$borderColor}";
        }

        // Font family
        if ($style['font_family'] ?? null) {
            $css[] = "font-family: '{$style['font_family']}', sans-serif";
        }

        return implode('; ', $css);
    }

    /** @return array<string, mixed> */
    public static function defaultStyle(): array
    {
        return [
            'bg_type' => 'solid',
            'bg_color' => null,
            'bg_gradient_from' => null,
            'bg_gradient_to' => null,
            'bg_gradient_direction' => 'to-b',
            'text_color' => null,
            'padding_preset' => 'normal',
            'border_radius' => 'none',
            'border_top' => false,
            'border_bottom' => false,
            'border_color' => null,
            'font_family' => null,
        ];
    }

    public static function googleFontsUrl(array $fontFamilies): ?string
    {
        $unique = array_unique(array_filter($fontFamilies));

        if (empty($unique)) {
            return null;
        }

        $families = array_map(
            fn (string $f) => 'family=' . urlencode($f) . ':wght@300;400;600;700',
            $unique
        );

        return 'https://fonts.googleapis.com/css2?' . implode('&', $families) . '&display=swap';
    }
}
