@props(['section', 'data' => []])
@php
    $config = $section->config;
    $brands = $data['brands'] ?? collect();
    $style = $config['style'] ?? [];
    $customCss = \App\Support\SectionStyleHelper::toInlineCss($style);
    $hasCustomStyle = $customCss !== '';
    $customBgColor = $style['bg_color'] ?? null;
@endphp

@if($brands->count() > 0)
    <section class="{{ $hasCustomStyle ? 'overflow-hidden' : 'py-12 bg-white dark:bg-zinc-900 border-b border-gray-100 dark:border-zinc-800 overflow-hidden' }}"
        @if($hasCustomStyle) style="{!! $customCss !!}" @endif>
        <div class="container mx-auto px-4 mb-8">
            <h2 class="text-2xl md:text-3xl font-bold font-heading {{ $hasCustomStyle ? '' : 'text-gray-900 dark:text-white' }} text-center">
                {{ __($config['heading'] ?? 'Marcas con las que Trabajamos') }}
            </h2>
            @if($config['subheading'] ?? null)
                <p class="{{ $hasCustomStyle ? 'opacity-70' : 'text-gray-500 dark:text-gray-400' }} text-center mt-2">{{ __($config['subheading']) }}</p>
            @endif
        </div>

        <div class="relative" x-data="{ paused: false }">
            @if($customBgColor)
                <div class="absolute left-0 top-0 bottom-0 w-20 z-10 pointer-events-none" style="background: linear-gradient(to right, {{ $customBgColor }}, transparent)"></div>
                <div class="absolute right-0 top-0 bottom-0 w-20 z-10 pointer-events-none" style="background: linear-gradient(to left, {{ $customBgColor }}, transparent)"></div>
            @else
                <div class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-white dark:from-zinc-900 to-transparent z-10 pointer-events-none"></div>
                <div class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-white dark:from-zinc-900 to-transparent z-10 pointer-events-none"></div>
            @endif

            <div class="flex brand-scroll-track gap-12 items-center py-4"
                :class="{ 'animation-paused': paused }"
                @mouseenter="paused = true"
                @mouseleave="paused = false">
                @foreach($brands as $brand)
                    <a href="{{ route('brands.index') }}"
                        class="flex-shrink-0 group" title="{{ $brand->name }}">
                        @if($brand->logo_url)
                            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                                class="h-12 md:h-16 w-auto object-contain opacity-60 grayscale group-hover:opacity-100 group-hover:grayscale-0 transition duration-300">
                        @else
                            <span class="h-12 md:h-16 flex items-center px-6 text-lg font-bold text-gray-400 group-hover:text-primary-600 transition duration-300 whitespace-nowrap">
                                {{ $brand->name }}
                            </span>
                        @endif
                    </a>
                @endforeach
                {{-- Duplicate for seamless loop --}}
                @foreach($brands as $brand)
                    <a href="{{ route('brands.index') }}"
                        class="flex-shrink-0 group" title="{{ $brand->name }}">
                        @if($brand->logo_url)
                            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                                class="h-12 md:h-16 w-auto object-contain opacity-60 grayscale group-hover:opacity-100 group-hover:grayscale-0 transition duration-300">
                        @else
                            <span class="h-12 md:h-16 flex items-center px-6 text-lg font-bold text-gray-400 group-hover:text-primary-600 transition duration-300 whitespace-nowrap">
                                {{ $brand->name }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
