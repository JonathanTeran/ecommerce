@props(['data' => []])
@php
    $bgColor = $data['bg_color'] ?? null;
@endphp

<section
    class="{{ $bgColor ? '' : 'bg-primary-900' }} py-16 overflow-hidden"
    @if($bgColor) style="background-color: {{ $bgColor }}" @endif
>
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white font-heading mb-4">
            {{ $data['heading'] ?? '' }}
        </h2>
        @if($data['description'] ?? null)
            <p class="text-lg text-white/80 max-w-2xl mx-auto mb-8">
                {{ $data['description'] }}
            </p>
        @endif
        @if($data['button_text'] ?? null)
            <a href="{{ $data['button_url'] ?? '#' }}"
                class="inline-flex items-center gap-2 px-8 py-4 bg-white text-slate-900 font-bold rounded-full hover:bg-slate-100 transition duration-300 shadow-lg">
                {{ $data['button_text'] }}
            </a>
        @endif
    </div>
</section>
