@props(['section', 'data' => []])
@php
    $config = $section->config;
@endphp

<section class="relative overflow-hidden" style="background: #f8f5f0; min-height: 85vh;">
    <div class="container mx-auto px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 min-h-[85vh] items-center">
            {{-- Left: Content --}}
            <div class="py-16 lg:py-24 max-w-lg">
                @if($config['badge_text'] ?? null)
                    <p class="text-xs font-medium tracking-[0.3em] uppercase mb-6" style="color: #b8860b; letter-spacing: 0.3em;">
                        {{ $config['badge_text'] }}
                    </p>
                @endif

                <h1 class="text-5xl lg:text-6xl font-light leading-tight" style="color: #1a1a1a; font-family: 'Playfair Display', Georgia, serif;">
                    {{ $config['heading'] ?? 'Nueva Coleccion' }}
                </h1>

                @if($config['subheading'] ?? null)
                    <p class="mt-6 text-base leading-relaxed" style="color: #666666; font-family: 'Inter', sans-serif;">
                        {{ $config['subheading'] }}
                    </p>
                @endif

                <div class="mt-10 flex gap-4">
                    @if($config['cta_text'] ?? null)
                        <a href="{{ $config['cta_url'] ?? '/shop' }}"
                            class="inline-flex items-center gap-3 px-8 py-4 text-sm font-medium uppercase tracking-widest transition-all duration-300 border-b-2"
                            style="background: transparent; color: #1a1a1a; border-color: #1a1a1a;">
                            {{ $config['cta_text'] }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    @endif
                </div>

                {{-- Season tag --}}
                <div class="mt-16 flex items-center gap-6">
                    <div class="h-px flex-1" style="background: #d4c5a9;"></div>
                    <span class="text-xs tracking-[0.2em] uppercase" style="color: #b8860b;">Primavera / Verano 2026</span>
                    <div class="h-px flex-1" style="background: #d4c5a9;"></div>
                </div>
            </div>

            {{-- Right: Image --}}
            <div class="relative h-full hidden lg:flex items-end justify-center pb-0">
                @if($config['background_image'] ?? null)
                    <img src="{{ asset('storage/' . $config['background_image']) }}" alt=""
                        class="h-[75vh] w-auto object-cover object-top">
                @else
                    <div class="h-[70vh] w-80 flex items-center justify-center" style="background: linear-gradient(180deg, #ede8e0, #d4c5a9);">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto" style="color: #b8a88a;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            <p class="text-xs mt-2" style="color: #8a7d6b;">Modelo</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
