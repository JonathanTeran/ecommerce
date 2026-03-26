<?php

namespace App\Support;

class ThemeColors
{
    /** @return array<string, string>|null */
    public static function get(string $colorName): ?array
    {
        return self::PALETTES[$colorName] ?? null;
    }

    /**
     * Predefined color palettes that override the --color-primary-* CSS custom properties.
     *
     * @var array<string, array<string, string>>
     */
    private const PALETTES = [
        'blue' => [
            '--color-primary-50' => '#eff6ff',
            '--color-primary-100' => '#dbeafe',
            '--color-primary-200' => '#bfdbfe',
            '--color-primary-300' => '#93c5fd',
            '--color-primary-400' => '#60a5fa',
            '--color-primary-500' => '#3b82f6',
            '--color-primary-600' => '#2563eb',
            '--color-primary-700' => '#1d4ed8',
            '--color-primary-800' => '#1e40af',
            '--color-primary-900' => '#1e3a8a',
            '--color-primary-950' => '#172554',
        ],
        'red' => [
            '--color-primary-50' => '#fef2f2',
            '--color-primary-100' => '#fee2e2',
            '--color-primary-200' => '#fecaca',
            '--color-primary-300' => '#fca5a5',
            '--color-primary-400' => '#f87171',
            '--color-primary-500' => '#ef4444',
            '--color-primary-600' => '#dc2626',
            '--color-primary-700' => '#b91c1c',
            '--color-primary-800' => '#991b1b',
            '--color-primary-900' => '#7f1d1d',
            '--color-primary-950' => '#450a0a',
        ],
        'green' => [
            '--color-primary-50' => '#f0fdf4',
            '--color-primary-100' => '#dcfce7',
            '--color-primary-200' => '#bbf7d0',
            '--color-primary-300' => '#86efac',
            '--color-primary-400' => '#4ade80',
            '--color-primary-500' => '#22c55e',
            '--color-primary-600' => '#16a34a',
            '--color-primary-700' => '#15803d',
            '--color-primary-800' => '#166534',
            '--color-primary-900' => '#14532d',
            '--color-primary-950' => '#052e16',
        ],
        'purple' => [
            '--color-primary-50' => '#faf5ff',
            '--color-primary-100' => '#f3e8ff',
            '--color-primary-200' => '#e9d5ff',
            '--color-primary-300' => '#d8b4fe',
            '--color-primary-400' => '#c084fc',
            '--color-primary-500' => '#a855f7',
            '--color-primary-600' => '#9333ea',
            '--color-primary-700' => '#7e22ce',
            '--color-primary-800' => '#6b21a8',
            '--color-primary-900' => '#581c87',
            '--color-primary-950' => '#3b0764',
        ],
        'orange' => [
            '--color-primary-50' => '#fff7ed',
            '--color-primary-100' => '#ffedd5',
            '--color-primary-200' => '#fed7aa',
            '--color-primary-300' => '#fdba74',
            '--color-primary-400' => '#fb923c',
            '--color-primary-500' => '#f97316',
            '--color-primary-600' => '#ea580c',
            '--color-primary-700' => '#c2410c',
            '--color-primary-800' => '#9a3412',
            '--color-primary-900' => '#7c2d12',
            '--color-primary-950' => '#431407',
        ],
        'teal' => [
            '--color-primary-50' => '#f0fdfa',
            '--color-primary-100' => '#ccfbf1',
            '--color-primary-200' => '#99f6e4',
            '--color-primary-300' => '#5eead4',
            '--color-primary-400' => '#2dd4bf',
            '--color-primary-500' => '#14b8a6',
            '--color-primary-600' => '#0d9488',
            '--color-primary-700' => '#0f766e',
            '--color-primary-800' => '#115e59',
            '--color-primary-900' => '#134e4a',
            '--color-primary-950' => '#042f2e',
        ],
        'pink' => [
            '--color-primary-50' => '#fdf2f8',
            '--color-primary-100' => '#fce7f3',
            '--color-primary-200' => '#fbcfe8',
            '--color-primary-300' => '#f9a8d4',
            '--color-primary-400' => '#f472b6',
            '--color-primary-500' => '#ec4899',
            '--color-primary-600' => '#db2777',
            '--color-primary-700' => '#be185d',
            '--color-primary-800' => '#9d174d',
            '--color-primary-900' => '#831843',
            '--color-primary-950' => '#500724',
        ],
        'indigo' => [
            '--color-primary-50' => '#eef2ff',
            '--color-primary-100' => '#e0e7ff',
            '--color-primary-200' => '#c7d2fe',
            '--color-primary-300' => '#a5b4fc',
            '--color-primary-400' => '#818cf8',
            '--color-primary-500' => '#6366f1',
            '--color-primary-600' => '#4f46e5',
            '--color-primary-700' => '#4338ca',
            '--color-primary-800' => '#3730a3',
            '--color-primary-900' => '#312e81',
            '--color-primary-950' => '#1e1b4e',
        ],
    ];
}
