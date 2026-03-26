<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $tenantSettings?->site_name ?? config('app.name', 'Laravel') }}</title>

    @if($tenantSettings?->site_favicon)
        <link rel="icon" href="{{ url('storage/' . $tenantSettings->site_favicon) }}" type="image/x-icon">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    {{ \App\Support\ViteAssets::tags(['resources/css/app.css', 'resources/js/app.js']) }}

    @if($currentTenant?->theme_color && $currentTenant->theme_color !== 'indigo')
        <style>
            @php
                $colors = \App\Support\ThemeColors::get($currentTenant->theme_color);
            @endphp
            @if($colors)
                :root {
                    @foreach($colors as $key => $value)
                        {{ $key }}: {{ $value }};
                    @endforeach
                }
            @endif
        </style>
    @endif

    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-dark-bg">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            {{ $slot }}
        </div>
    </div>
    @livewireScripts
</body>

</html>
