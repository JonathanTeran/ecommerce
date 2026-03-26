@props(['data' => []])
@php
    $items = $data['items'] ?? [];
@endphp

@if(count($items) > 0)
    <section class="py-16 bg-slate-50 dark:bg-zinc-900">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-{{ min(count($items), 3) }} gap-8">
                @foreach($items as $item)
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl p-8 shadow-sm">
                        <svg class="w-8 h-8 text-primary-300 dark:text-primary-700 mb-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"></path>
                        </svg>
                        <p class="text-slate-600 dark:text-slate-300 leading-relaxed mb-6 italic">
                            "{{ $item['quote'] ?? '' }}"
                        </p>
                        <div class="flex items-center gap-3">
                            @if($item['avatar'] ?? null)
                                <img src="{{ asset('storage/' . $item['avatar']) }}" alt="{{ $item['name'] ?? '' }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold">
                                    {{ strtoupper(substr($item['name'] ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $item['name'] ?? '' }}</p>
                                @if($item['role'] ?? null)
                                    <p class="text-slate-500 dark:text-zinc-400 text-xs">{{ $item['role'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
