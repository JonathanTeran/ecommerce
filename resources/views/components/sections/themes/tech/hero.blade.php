@props(['section', 'data' => []])
@php
    $config = $section->config;
@endphp

<section class="relative overflow-hidden" style="background: #0a0a0a; min-height: 85vh;">
    {{-- Animated grid background --}}
    <div class="absolute inset-0" style="background-image: linear-gradient(rgba(0,255,136,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(0,255,136,0.03) 1px, transparent 1px); background-size: 60px 60px;"></div>

    {{-- Glow effect --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full" style="background: radial-gradient(circle, rgba(0,255,136,0.08) 0%, transparent 70%);"></div>

    <div class="container mx-auto px-6 lg:px-8 relative z-10">
        <div class="flex flex-col items-center justify-center min-h-[85vh] text-center">
            @if($config['badge_text'] ?? null)
                <div class="inline-flex items-center gap-2 rounded-full border px-5 py-2 mb-8" style="border-color: rgba(0,255,136,0.3); background: rgba(0,255,136,0.05);">
                    <span class="h-2 w-2 rounded-full animate-pulse" style="background: #00ff88;"></span>
                    <span class="text-xs font-mono font-medium tracking-widest uppercase" style="color: #00ff88;">{{ $config['badge_text'] }}</span>
                </div>
            @endif

            <h1 class="text-5xl md:text-7xl lg:text-8xl font-black tracking-tighter leading-none" style="color: #ffffff; font-family: 'Inter', system-ui, sans-serif;">
                {{ $config['heading'] ?? 'NEXT LEVEL TECH' }}
            </h1>

            {{-- Accent line --}}
            <div class="w-24 h-1 mt-6 rounded-full" style="background: linear-gradient(90deg, #00ff88, #00cc6a);"></div>

            @if($config['subheading'] ?? null)
                <p class="mt-8 text-lg max-w-2xl mx-auto leading-relaxed" style="color: #a3a3a3; font-family: 'Inter', sans-serif;">
                    {{ $config['subheading'] }}
                </p>
            @endif

            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                @if($config['cta_text'] ?? null)
                    <a href="{{ $config['cta_url'] ?? '/shop' }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg px-8 py-4 text-sm font-bold uppercase tracking-wider transition-all duration-200"
                        style="background: #00ff88; color: #0a0a0a;">
                        {{ $config['cta_text'] }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                @endif
                @if($config['secondary_cta_text'] ?? null)
                    <a href="{{ $config['secondary_cta_url'] ?? '/categories' }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border px-8 py-4 text-sm font-bold uppercase tracking-wider transition-all duration-200"
                        style="border-color: #333333; color: #e5e5e5; background: rgba(255,255,255,0.03);">
                        {{ $config['secondary_cta_text'] }}
                    </a>
                @endif
            </div>

            {{-- Specs badges --}}
            <div class="mt-16 flex flex-wrap gap-6 justify-center">
                @php
                    $specs = [
                        ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Garantia Oficial'],
                        ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'text' => 'Envio Express'],
                        ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'text' => 'Pago Seguro'],
                    ];
                @endphp
                @foreach($specs as $spec)
                    <div class="flex items-center gap-2 rounded-lg px-4 py-2" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                        <svg class="w-4 h-4" style="color: #00ff88;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $spec['icon'] }}"></path></svg>
                        <span class="text-xs font-medium" style="color: #a3a3a3;">{{ $spec['text'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
