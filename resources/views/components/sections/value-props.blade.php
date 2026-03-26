@props(['section', 'data' => []])
@php
    $config = $section->config;
    $items = $config['items'] ?? [];
    $icons = [
        'truck' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'check-circle' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'refresh' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        'support' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z',
        'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'star' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
    ];
@endphp

@php
    $style = $config['style'] ?? [];
    $customCss = \App\Support\SectionStyleHelper::toInlineCss($style);
    $hasCustomStyle = $customCss !== '';
@endphp

@if(count($items) > 0)
    <section class="{{ $hasCustomStyle ? '' : 'py-20 bg-zinc-900 text-white' }}"
        @if($hasCustomStyle) style="{!! $customCss !!}" @endif>
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-{{ min(count($items), 4) }} gap-8 text-center md:text-left">
                @foreach($items as $item)
                    <div class="p-6 rounded-2xl bg-white/5 backdrop-blur-sm border border-white/10 hover:border-gold-500/50 transition duration-300">
                        <div class="w-12 h-12 bg-gold-500 rounded-full flex items-center justify-center mb-6 mx-auto md:mx-0">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $icons[$item['icon'] ?? 'star'] ?? $icons['star'] }}"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">{{ __($item['title'] ?? '') }}</h3>
                        <p class="text-gray-400 font-light">{{ __($item['description'] ?? '') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
