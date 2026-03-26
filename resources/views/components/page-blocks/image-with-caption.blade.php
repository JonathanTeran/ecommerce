@props(['data' => []])
@php
    $alignment = $data['alignment'] ?? 'center';
    $alignClass = match($alignment) {
        'left' => 'text-left',
        'right' => 'text-right',
        default => 'text-center',
    };
@endphp

<section class="py-12 bg-white dark:bg-zinc-950">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 {{ $alignClass }}">
        @if($data['image'] ?? null)
            <img
                src="{{ asset('storage/' . $data['image']) }}"
                alt="{{ $data['caption'] ?? '' }}"
                class="rounded-2xl shadow-lg {{ $alignment === 'center' ? 'mx-auto' : '' }} max-w-full h-auto"
            >
        @endif
        @if($data['caption'] ?? null)
            <p class="mt-4 text-sm text-slate-500 dark:text-zinc-400 italic">
                {{ $data['caption'] }}
            </p>
        @endif
    </div>
</section>
