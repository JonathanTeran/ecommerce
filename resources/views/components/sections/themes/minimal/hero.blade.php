@props(['section', 'data' => []])
@php
    $config = $section->config;
    $style = $config['style'] ?? [];
@endphp

<section class="relative overflow-hidden" style="background: #ffffff; min-height: 80vh;">
    {{-- Subtle grid pattern --}}
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=60 height=60 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cpath d=%27M0 0h60v60H0z%27 fill=%27none%27 stroke=%27%23000%27 stroke-width=%270.5%27/%3E%3C/svg%3E');"></div>

    <div class="container mx-auto px-6 lg:px-8 py-24 lg:py-32">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            {{-- Left: Content --}}
            <div class="max-w-xl">
                @if($config['badge_text'] ?? null)
                    <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-1.5 mb-8">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs font-medium text-gray-600 tracking-wide uppercase">{{ $config['badge_text'] }}</span>
                    </div>
                @endif

                <h1 class="text-5xl lg:text-7xl font-bold tracking-tight leading-none" style="color: #111827; font-family: 'Inter', system-ui, sans-serif;">
                    {{ $config['heading'] ?? 'Bienvenido' }}
                </h1>

                @if($config['subheading'] ?? null)
                    <p class="mt-8 text-lg leading-relaxed" style="color: #6b7280;">
                        {{ $config['subheading'] }}
                    </p>
                @endif

                <div class="mt-10 flex flex-col sm:flex-row gap-4">
                    @if($config['cta_text'] ?? null)
                        <a href="{{ $config['cta_url'] ?? '/shop' }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full px-8 py-4 text-sm font-semibold transition-all duration-200"
                            style="background: #111827; color: #ffffff;">
                            {{ $config['cta_text'] }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    @endif
                    @if($config['secondary_cta_text'] ?? null)
                        <a href="{{ $config['secondary_cta_url'] ?? '/categories' }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full border px-8 py-4 text-sm font-semibold transition-all duration-200"
                            style="border-color: #e5e7eb; color: #374151;">
                            {{ $config['secondary_cta_text'] }}
                        </a>
                    @endif
                </div>

                {{-- Trust signals --}}
                <div class="mt-12 flex items-center gap-8">
                    <div class="flex -space-x-2">
                        @for($i = 0; $i < 4; $i++)
                            <div class="h-10 w-10 rounded-full border-2 border-white flex items-center justify-center text-xs font-bold" style="background: {{ ['#f0abfc', '#93c5fd', '#86efac', '#fde68a'][$i] }}; color: #374151;">
                                {{ ['AM', 'JT', 'LR', 'KP'][$i] }}
                            </div>
                        @endfor
                    </div>
                    <div>
                        <div class="flex items-center gap-1">
                            @for($s = 0; $s < 5; $s++)
                                <svg class="w-4 h-4" style="color: #fbbf24;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            @endfor
                        </div>
                        <p class="text-xs mt-1" style="color: #9ca3af;">+2,500 clientes satisfechos</p>
                    </div>
                </div>
            </div>

            {{-- Right: Product showcase / Image --}}
            <div class="relative hidden lg:block">
                <div class="absolute -inset-4 rounded-3xl" style="background: linear-gradient(135deg, #f9fafb, #f3f4f6);"></div>
                @if($config['background_image'] ?? null)
                    <img src="{{ asset('storage/' . $config['background_image']) }}" alt=""
                        class="relative rounded-2xl shadow-2xl shadow-gray-200/50 w-full aspect-[4/3] object-cover">
                @else
                    <div class="relative rounded-2xl w-full aspect-[4/3] flex items-center justify-center" style="background: linear-gradient(135deg, #f1f5f9, #e2e8f0);">
                        <div class="text-center p-8">
                            <svg class="w-20 h-20 mx-auto mb-4" style="color: #cbd5e1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <p class="text-sm font-medium" style="color: #94a3b8;">Agrega una imagen desde el panel</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
