<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="{{ $robotsContent }}">

    <title>{{ $pageTitle }}</title>

    @if(!empty($pageDescription))
        <meta name="description" content="{{ \Illuminate\Support\Str::limit($pageDescription, 160) }}">
    @endif
    @if(!empty($seoConfig['home_meta_keywords']))
        <meta name="keywords" content="{{ $seoConfig['home_meta_keywords'] }}">
    @endif

    <link rel="canonical" href="{{ $pageCanonical }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $ogTitle ?? $pageTitle }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $pageCanonical }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    @if(!empty($pageDescription))
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($pageDescription, 200) }}">
    @endif
    @if($pageOgImage)
        <meta property="og:image" content="{{ $pageOgImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    @if(!empty($seoConfig['twitter_handle']))
        <meta name="twitter:site" content="{{ '@' . ltrim($seoConfig['twitter_handle'], '@') }}">
    @endif
    <meta name="twitter:title" content="{{ $ogTitle ?? $pageTitle }}">
    @if(!empty($pageDescription))
        <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit($pageDescription, 200) }}">
    @endif
    @if($pageOgImage)
        <meta name="twitter:image" content="{{ $pageOgImage }}">
    @endif

    {{-- Verification Codes --}}
    @if(!empty($seoConfig['google_site_verification']))
        <meta name="google-site-verification" content="{{ $seoConfig['google_site_verification'] }}">
    @endif
    @if(!empty($seoConfig['bing_site_verification']))
        <meta name="msvalidate.01" content="{{ $seoConfig['bing_site_verification'] }}">
    @endif

    @if($tenantSettings?->site_favicon)
        <link rel="icon" href="{{ url('storage/' . $tenantSettings->site_favicon) }}" type="image/x-icon">
    @endif

    {{-- Structured Data: Organization --}}
    @if(!empty($seoConfig['org_name']))
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $seoConfig['org_name'],
            'description' => $seoConfig['org_description'] ?? '',
            'url' => url('/'),
            'logo' => $tenantSettings?->site_logo ? url('storage/' . $tenantSettings->site_logo) : '',
            'telephone' => $seoConfig['org_phone'] ?? '',
            'email' => $seoConfig['org_email'] ?? '',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $seoConfig['org_address'] ?? '',
                'addressLocality' => $seoConfig['org_city'] ?? '',
                'addressCountry' => $seoConfig['org_country'] ?? '',
            ],
            'sameAs' => array_values(array_filter([
                $tenantSettings?->getSocialLinks()['facebook_url'] ?? null,
                $tenantSettings?->getSocialLinks()['instagram_url'] ?? null,
                $tenantSettings?->getSocialLinks()['twitter_url'] ?? null,
                $tenantSettings?->getSocialLinks()['youtube_url'] ?? null,
                $tenantSettings?->getSocialLinks()['tiktok_url'] ?? null,
            ])),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    {{-- Structured Data: WebSite + SearchAction --}}
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => url('/shop?q={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    {{-- Structured Data: BreadcrumbList (if breadcrumbs provided) --}}
    @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)->map(fn($b, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $b['name'],
                'item' => $b['url'] ?? null,
            ])->toArray(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    {{-- Page-specific Structured Data --}}
    @if(isset($jsonLd))
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    <!-- Scripts -->
    {{ \App\Support\ViteAssets::tags(['resources/css/app.css', 'resources/js/app.js']) }}

    @php
        $activeThemeColor = $tenantSettings?->theme_color ?? $currentTenant?->theme_color;
    @endphp
    @if($activeThemeColor && $activeThemeColor !== 'indigo')
        <style>
            @php $colors = \App\Support\ThemeColors::get($activeThemeColor); @endphp
            @if($colors)
                :root { @foreach($colors as $key => $value) {{ $key }}: {{ $value }}; @endforeach }
            @endif
        </style>
    @endif

    @php
        $typoConfig = $tenantSettings?->getTypographyConfig() ?? [];
        $bodyFont = $typoConfig['body_font'] ?? '';
        $headingFont = $typoConfig['heading_font'] ?? '';
        $fontScale = $typoConfig['font_size_scale'] ?? 'normal';
        $fontsToLoad = array_filter([$bodyFont, $headingFont]);
        $googleFontsUrl = !empty($fontsToLoad) ? \App\Support\SectionStyleHelper::googleFontsUrl($fontsToLoad) : null;
    @endphp
    @if($googleFontsUrl)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $googleFontsUrl }}" rel="stylesheet">
    @endif
    @if(!empty($bodyFont) || !empty($headingFont) || $fontScale !== 'normal')
        <style>
            @if(!empty($bodyFont)) body { font-family: '{{ $bodyFont }}', sans-serif; } @endif
            @if(!empty($headingFont)) h1, h2, h3, h4, h5, h6, .font-heading { font-family: '{{ $headingFont }}', sans-serif; } @endif
            @if($fontScale === 'small') html { font-size: 14px; } @elseif($fontScale === 'large') html { font-size: 18px; } @endif
        </style>
    @endif
    @if(!empty($tenantSettings?->custom_css))
        <style>{!! strip_tags($tenantSettings->custom_css) !!}</style>
    @endif

    {{-- Google Analytics --}}
    @if(!empty($seoConfig['google_analytics_id']))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seoConfig['google_analytics_id'] }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $seoConfig['google_analytics_id'] }}');</script>
    @endif

    {{-- Google Tag Manager --}}
    @if(!empty($seoConfig['google_tag_manager_id']))
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $seoConfig['google_tag_manager_id'] }}');</script>
    @endif

    {{-- Facebook Pixel --}}
    @if(!empty($seoConfig['facebook_pixel_id']))
        <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{{ $seoConfig['facebook_pixel_id'] }}');fbq('track','PageView');</script>
    @endif

    {{-- TikTok Pixel --}}
    @if(!empty($seoConfig['tiktok_pixel_id']))
        <script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var a=document.createElement("script");a.type="text/javascript",a.async=!0,a.src=r+"?sdkid="+e+"&lib="+t;var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(a,s)}}(window,document,"ttq");ttq.load('{{ $seoConfig['tiktok_pixel_id'] }}');ttq.page();</script>
    @endif

    {{-- Ecommerce Tracking Config --}}
    <script>
        window.__TRACKING__ = {
            hasFbq: {{ !empty($seoConfig['facebook_pixel_id']) ? 'true' : 'false' }},
            hasTtq: {{ !empty($seoConfig['tiktok_pixel_id']) ? 'true' : 'false' }},
            hasGtm: {{ !empty($seoConfig['google_tag_manager_id']) ? 'true' : 'false' }},
            hasGa4: {{ !empty($seoConfig['google_analytics_id']) ? 'true' : 'false' }},
            currency: @json($tenantSettings?->currency_code ?? 'USD')
        };
    </script>

    @stack('head')
</head>

<body class="font-sans antialiased bg-slate-50 dark:bg-dark-bg text-slate-900 dark:text-slate-100 selection:bg-gold-400 selection:text-black">
    <script>window.__CURRENCY_SYMBOL__ = @json($currencySymbol);</script>
    {{-- GTM noscript --}}
    @if(!empty($seoConfig['google_tag_manager_id']))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $seoConfig['google_tag_manager_id'] }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    <x-navbar />

    <main class="min-h-screen">
        {{ $slot }}
    </main>

    <x-footer />
    @livewire('auth.modals')
</body>

</html>
