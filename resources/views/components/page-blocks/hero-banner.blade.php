@props(['data' => []])

<div class="relative w-full h-[400px] md:h-[500px] flex items-center overflow-hidden bg-black">
    <div class="absolute inset-0 z-0">
        @if($data['background_image'] ?? null)
            <img src="{{ asset('storage/' . $data['background_image']) }}" alt="{{ $data['heading'] ?? '' }}" class="w-full h-full object-cover opacity-60">
        @else
            <div class="w-full h-full bg-linear-to-br from-primary-600 to-primary-900"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent"></div>
    </div>

    <div class="relative z-10 container mx-auto px-4">
        <div class="max-w-2xl text-white">
            <h2 class="text-4xl md:text-6xl font-bold font-heading mb-4 tracking-tight leading-tight">
                {{ $data['heading'] ?? '' }}
            </h2>
            @if($data['subheading'] ?? null)
                <p class="text-lg md:text-xl font-light mb-8 text-gray-300">
                    {{ $data['subheading'] }}
                </p>
            @endif
            @if($data['cta_text'] ?? null)
                <a href="{{ $data['cta_url'] ?? '#' }}"
                    class="inline-flex items-center gap-2 px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full transition duration-300 shadow-lg hover:shadow-primary-500/50">
                    {{ $data['cta_text'] }}
                </a>
            @endif
        </div>
    </div>
</div>
