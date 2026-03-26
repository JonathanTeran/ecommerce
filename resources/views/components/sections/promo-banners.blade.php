@props(['section', 'data' => []])
@php
    $config = $section->config;
    $banners = $config['banners'] ?? [];
    $badgeColors = [
        'red' => 'bg-red-600',
        'blue' => 'bg-blue-600',
        'green' => 'bg-green-600',
        'purple' => 'bg-purple-600',
        'orange' => 'bg-orange-600',
        'yellow' => 'bg-yellow-600',
        'teal' => 'bg-teal-600',
        'pink' => 'bg-pink-600',
    ];
    $hoverColors = [
        'red' => 'hover:text-red-400',
        'blue' => 'hover:text-blue-400',
        'green' => 'hover:text-green-400',
        'purple' => 'hover:text-purple-400',
        'orange' => 'hover:text-orange-400',
        'yellow' => 'hover:text-yellow-400',
        'teal' => 'hover:text-teal-400',
        'pink' => 'hover:text-pink-400',
    ];
@endphp

@php
    $style = $config['style'] ?? [];
    $customCss = \App\Support\SectionStyleHelper::toInlineCss($style);
    $hasCustomStyle = $customCss !== '';
@endphp

@if(count($banners) > 0)
    <section class="{{ $hasCustomStyle ? '' : 'py-12 bg-gray-50 dark:bg-zinc-950' }}"
        @if($hasCustomStyle) style="{!! $customCss !!}" @endif>
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-{{ min(count($banners), 2) }} gap-6">
                @foreach($banners as $banner)
                    @php
                        $bannerGradientStyles = [
                            'background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);',
                            'background: linear-gradient(135deg, #312e81 0%, #0f172a 100%);',
                            'background: linear-gradient(135deg, #581c87 0%, #0f172a 100%);',
                        ];
                        $bannerStyle = $bannerGradientStyles[$loop->index % count($bannerGradientStyles)];
                    @endphp
                    <div class="relative h-64 md:h-80 rounded-3xl overflow-hidden group"
                        @if(!($banner['image'] ?? null)) style="{{ $bannerStyle }}" @endif>
                        @if($banner['image'] ?? null)
                            <img src="{{ asset('storage/' . $banner['image']) }}"
                                alt="{{ $banner['title'] ?? '' }}"
                                class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700">
                            <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,0.85), rgba(0,0,0,0.2), transparent);"></div>
                        @else
                            <div class="absolute inset-0" style="opacity: 0.15;">
                                <svg class="absolute bottom-0 right-0 w-64 h-64" style="color: rgba(255,255,255,0.3);" fill="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                        @endif
                        <div class="absolute bottom-6 left-6 md:bottom-10 md:left-10" style="color: #ffffff;">
                            @if($banner['badge_text'] ?? null)
                                @php
                                    $badgeHexColors = ['red' => '#dc2626', 'blue' => '#2563eb', 'green' => '#16a34a', 'purple' => '#9333ea', 'orange' => '#ea580c', 'yellow' => '#ca8a04', 'teal' => '#0d9488', 'pink' => '#db2777'];
                                    $badgeHex = $badgeHexColors[$banner['badge_color'] ?? 'blue'] ?? '#2563eb';
                                @endphp
                                <span class="text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-2 inline-block" style="background-color: {{ $badgeHex }}; color: #ffffff;">
                                    {{ $banner['badge_text'] }}
                                </span>
                            @endif
                            <h3 class="text-3xl font-bold font-heading mb-2" style="color: #ffffff;">{{ $banner['title'] ?? '' }}</h3>
                            @if($banner['subtitle'] ?? null)
                                <p class="mb-4 max-w-xs" style="color: #e5e7eb;">{{ $banner['subtitle'] }}</p>
                            @endif
                            @if($banner['button_text'] ?? null)
                                <a href="{{ $banner['button_url'] ?? '#' }}"
                                    class="inline-flex items-center text-sm font-bold transition" style="color: #ffffff;">
                                    {{ $banner['button_text'] }}
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
