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
                        $bannerGradients = ['bg-gradient-to-br from-slate-800 to-slate-900', 'bg-gradient-to-br from-indigo-900 to-slate-900', 'bg-gradient-to-br from-purple-900 to-slate-900'];
                        $bannerGradient = $bannerGradients[$loop->index % count($bannerGradients)];
                    @endphp
                    <div class="relative h-64 md:h-80 rounded-3xl overflow-hidden group {{ !($banner['image'] ?? null) ? $bannerGradient : '' }}">
                        @if($banner['image'] ?? null)
                            <img src="{{ asset('storage/' . $banner['image']) }}"
                                alt="{{ $banner['title'] ?? '' }}"
                                class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                        @else
                            <div class="absolute inset-0 opacity-20">
                                <svg class="absolute bottom-0 right-0 w-64 h-64 text-white/10" fill="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                        @endif
                        <div class="absolute bottom-6 left-6 md:bottom-10 md:left-10 text-white">
                            @if($banner['badge_text'] ?? null)
                                <span class="{{ $badgeColors[$banner['badge_color'] ?? 'blue'] ?? 'bg-blue-600' }} text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-2 inline-block">
                                    {{ $banner['badge_text'] }}
                                </span>
                            @endif
                            <h3 class="text-3xl font-bold font-heading mb-2">{{ $banner['title'] ?? '' }}</h3>
                            @if($banner['subtitle'] ?? null)
                                <p class="text-gray-200 mb-4 max-w-xs">{{ $banner['subtitle'] }}</p>
                            @endif
                            @if($banner['button_text'] ?? null)
                                <a href="{{ $banner['button_url'] ?? '#' }}"
                                    class="inline-flex items-center text-sm font-bold {{ $hoverColors[$banner['badge_color'] ?? 'blue'] ?? 'hover:text-blue-400' }} transition">
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
