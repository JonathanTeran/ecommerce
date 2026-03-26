@props(['data' => []])
@php
    $images = $data['images'] ?? [];
    $columns = $data['columns'] ?? 3;
@endphp

@if(count($images) > 0)
    <section class="py-16 bg-white dark:bg-zinc-950">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-{{ $columns }} gap-4">
                @foreach($images as $image)
                    <div class="rounded-xl overflow-hidden shadow-sm">
                        <img
                            src="{{ asset('storage/' . $image) }}"
                            alt=""
                            class="w-full h-64 object-cover hover:scale-105 transition-transform duration-300"
                        >
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
