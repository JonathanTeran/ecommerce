@props(['section', 'data' => []])
@php
    $config = $section->config;
    $reviews = $config['reviews'] ?? [];
    $style = $config['style'] ?? [];
    $customCss = \App\Support\SectionStyleHelper::toInlineCss($style);
    $hasCustomStyle = $customCss !== '';
@endphp

<section class="{{ $hasCustomStyle ? '' : 'py-20 bg-gray-50 dark:bg-zinc-900' }}"
    @if($hasCustomStyle) style="{!! $customCss !!}" @endif>
    <div class="container mx-auto px-4">
        {{-- Header --}}
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-bold font-heading mb-3" style="color: #111827;">
                {{ __($config['heading'] ?? 'Lo que dicen nuestros clientes') }}
            </h2>
            @if($config['subheading'] ?? null)
                <p class="text-lg max-w-2xl mx-auto" style="color: #64748b;">
                    {{ __($config['subheading']) }}
                </p>
            @endif
        </div>

        {{-- Reviews Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            @foreach($reviews as $review)
                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 dark:border-zinc-700 hover:shadow-lg transition-shadow duration-300 relative">
                    {{-- Quote icon --}}
                    <div class="absolute top-6 right-6 text-indigo-100 dark:text-indigo-900/30">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983z"/>
                        </svg>
                    </div>

                    {{-- Stars --}}
                    <div class="flex items-center gap-0.5 mb-4">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5" style="color: {{ $i <= ($review['rating'] ?? 5) ? '#fbbf24' : '#e5e7eb' }};" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>

                    {{-- Review text --}}
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-6 text-base italic">
                        "{{ $review['text'] ?? '' }}"
                    </p>

                    {{-- Author --}}
                    <div class="flex items-center gap-3 mt-auto">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ strtoupper(substr($review['author'] ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $review['author'] ?? 'Cliente' }}</p>
                            @if($review['role'] ?? null)
                                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    {{ $review['role'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
