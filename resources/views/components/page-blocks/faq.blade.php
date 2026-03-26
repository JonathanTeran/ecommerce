@props(['data' => []])
@php
    $items = $data['items'] ?? [];
@endphp

@if(count($items) > 0)
    <section class="py-16 bg-white dark:bg-zinc-950">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-4">
                @foreach($items as $index => $item)
                    <div x-data="{ open: false }" class="border border-slate-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                        <button
                            @click="open = !open"
                            class="w-full flex items-center justify-between px-6 py-4 text-left bg-slate-50 dark:bg-zinc-900 hover:bg-slate-100 dark:hover:bg-zinc-800 transition"
                        >
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $item['question'] ?? '' }}</span>
                            <svg class="w-5 h-5 text-slate-500 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition x-cloak class="px-6 py-4 bg-white dark:bg-zinc-950">
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ $item['answer'] ?? '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
