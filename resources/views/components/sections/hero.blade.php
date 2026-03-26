@props(['section', 'data' => []])
@php
    $config = $section->config;
    $style = $config['style'] ?? [];
    $heroCss = \App\Support\SectionStyleHelper::toInlineCss(array_merge($style, ['padding_preset' => null]));
    $hasCustomStyle = $heroCss !== '';
@endphp

<div class="relative w-full h-[500px] md:h-[600px] flex items-center overflow-hidden {{ $hasCustomStyle ? '' : 'bg-black' }}"
    @if($hasCustomStyle) style="{!! $heroCss !!}" @endif>
    {{-- Background Image --}}
    <div class="absolute inset-0 z-0">
        @if($config['background_image'] ?? null)
            <img src="{{ asset('storage/' . $config['background_image']) }}" alt="Hero Background" class="w-full h-full object-cover opacity-60">
        @else
            <div class="w-full h-full bg-linear-to-br from-primary-600 to-primary-900"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent"></div>
    </div>

    <div class="relative z-10 container mx-auto px-4 animate-fade-in-up">
        <div class="max-w-2xl text-white">
            @if($config['badge_text'] ?? null)
                <span
                    class="inline-block px-4 py-1 mb-4 text-xs font-bold tracking-wider text-primary-400 uppercase bg-primary-900/50 rounded-full border border-primary-500/30">
                    {{ __($config['badge_text']) }}
                </span>
            @endif
            <h1 class="text-5xl md:text-7xl font-bold font-heading mb-6 tracking-tight leading-tight">
                {{ __($config['heading'] ?? '') }}
            </h1>
            @if($config['subheading'] ?? null)
                <p class="text-xl md:text-2xl font-light mb-8 text-gray-300">
                    {{ __($config['subheading']) }}
                </p>
            @endif
            <div class="flex flex-col sm:flex-row gap-4">
                @if($config['cta_text'] ?? null)
                    <a href="{{ $config['cta_url'] ?? '#' }}"
                        class="px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full transition duration-300 shadow-lg hover:shadow-primary-500/50 flex items-center gap-2 justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        {{ __($config['cta_text']) }}
                    </a>
                @endif
                @if($config['secondary_cta_text'] ?? null)
                    <a href="{{ $config['secondary_cta_url'] ?? '#' }}"
                        class="px-8 py-4 border border-white/30 backdrop-blur-sm text-white font-semibold rounded-full hover:bg-white/10 transition duration-300 flex items-center gap-2 justify-center">
                        {{ __($config['secondary_cta_text']) }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
