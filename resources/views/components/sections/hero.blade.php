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

            {{-- Trust Badges --}}
            <div class="mt-8 grid grid-cols-2 md:flex md:flex-wrap gap-x-6 gap-y-3 text-sm text-gray-300/90">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ __('Envio gratis +$100') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>{{ __('Garantia 6-12 meses') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>{{ __('Devolucion facil 30 dias') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    <span>{{ __('Soporte tecnico') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
