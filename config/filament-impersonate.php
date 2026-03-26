<?php

return [
    'guard' => env('FILAMENT_IMPERSONATE_GUARD', 'web'),
    'redirect_to' => env('FILAMENT_IMPERSONATE_REDIRECT', '/admin'),
    'leave_middleware' => env('FILAMENT_IMPERSONATE_LEAVE_MIDDLEWARE', 'web'),

    'banner' => [
        'render_hook' => env('FILAMENT_IMPERSONATE_BANNER_RENDER_HOOK', 'panels::body.start'),
        'style' => env('FILAMENT_IMPERSONATE_BANNER_STYLE', 'dark'),
        'fixed' => env('FILAMENT_IMPERSONATE_BANNER_FIXED', true),
        'position' => env('FILAMENT_IMPERSONATE_BANNER_POSITION', 'top'),

        'styles' => [
            'light' => [
                'text' => '#1f2937',
                'background' => '#f3f4f6',
                'border' => '#e8eaec',
            ],
            'dark' => [
                'text' => '#f3f4f6',
                'background' => '#1f2937',
                'border' => '#374151',
            ],
        ],
    ],
];
