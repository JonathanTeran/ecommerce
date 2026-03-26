@props(['section', 'data' => []])
@php
    $config = $section->config;
    $style = $config['style'] ?? [];
    $customCss = \App\Support\SectionStyleHelper::toInlineCss($style);
    $hasCustomStyle = $customCss !== '';
@endphp

<section class="{{ $hasCustomStyle ? 'overflow-hidden' : 'py-6 bg-primary-900 overflow-hidden' }}"
    @if($hasCustomStyle) style="{!! $customCss !!}" @endif>
    <div class="container mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center text-gold-400 shrink-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @switch($config['icon'] ?? 'puzzle')
                        @case('truck')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            @break
                        @case('check-circle')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            @break
                        @default
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z">
                            </path>
                    @endswitch
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-white mb-1">{{ __($config['heading'] ?? '') }}</h3>
                <p class="text-primary-200">{{ __($config['description'] ?? '') }}</p>
            </div>
        </div>
        @if($config['button_text'] ?? null)
            <a href="{{ $config['button_url'] ?? '#' }}"
                class="bg-gold-500 hover:bg-gold-400 text-black font-bold py-3 px-8 rounded-full shadow-lg transition transform hover:-translate-y-1">
                {{ __($config['button_text']) }}
            </a>
        @endif
    </div>
</section>
