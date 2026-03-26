@php
    $aboutConfig = $tenantSettings?->getAboutPageConfig() ?? [];
    $aboutTitle = $aboutConfig['title'] ?? '';
    $aboutDescription = $aboutConfig['description'] ?? '';
    $aboutValues = $aboutConfig['values'] ?? [];
    $aboutImage = $aboutConfig['team_image'] ?? '';
    $aboutCta = $aboutConfig['cta_text'] ?? '';
    $hasValues = !empty($aboutValues);

    $iconSvgs = [
        'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'bolt' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>',
        'support' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>',
        'truck' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>',
        'star' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>',
        'heart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>',
        'globe' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    ];
@endphp
<x-layouts.app>
    <div class="bg-white dark:bg-zinc-950 pt-24 pb-12">
        <div class="container mx-auto px-4">
            {{-- Hero Section --}}
            <div class="text-center max-w-3xl mx-auto mb-10 md:mb-16">
                <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold font-heading text-slate-900 dark:text-white mb-4 md:mb-6">
                    {{ !empty($aboutTitle) ? $aboutTitle : __('Our Story') }}
                </h1>
                <p class="text-base md:text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                    {{ !empty($aboutDescription) ? $aboutDescription : __('We are passionate about technology and lifestyle. Our mission is to bring you the best products to elevate your digital experience.') }}
                </p>
            </div>

            {{-- Values Grid --}}
            @if($hasValues)
                <div class="grid grid-cols-1 md:grid-cols-{{ min(count($aboutValues), 3) }} gap-8 mb-20">
                    @foreach($aboutValues as $value)
                        <div class="p-5 md:p-8 rounded-2xl bg-slate-50 dark:bg-zinc-900 text-center">
                            <div class="w-12 h-12 md:w-16 md:h-16 bg-primary-100 dark:bg-primary-900/30 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-4 md:mb-6">
                                <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $iconSvgs[$value['icon'] ?? 'check-circle'] ?? $iconSvgs['check-circle'] !!}
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">{{ $value['title'] ?? '' }}</h3>
                            <p class="text-slate-600 dark:text-slate-400">{{ $value['description'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Default values when nothing is configured --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
                    <div class="p-8 rounded-2xl bg-slate-50 dark:bg-zinc-900 text-center">
                        <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $iconSvgs['check-circle'] !!}
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">{{ __('Quality First') }}</h3>
                        <p class="text-slate-600 dark:text-slate-400">{{ __('We only stock products from trusted brands that meet our high standards for quality and performance.') }}</p>
                    </div>
                    <div class="p-8 rounded-2xl bg-slate-50 dark:bg-zinc-900 text-center">
                        <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $iconSvgs['bolt'] !!}
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">{{ __('Fast Shipping') }}</h3>
                        <p class="text-slate-600 dark:text-slate-400">{{ __('We understand you want your gear fast. We prioritize quick processing and reliable shipping.') }}</p>
                    </div>
                    <div class="p-8 rounded-2xl bg-slate-50 dark:bg-zinc-900 text-center">
                        <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $iconSvgs['support'] !!}
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">{{ __('Expert Support') }}</h3>
                        <p class="text-slate-600 dark:text-slate-400">{{ __('Our team of experts is here to help you find exactly what you need and answer any questions.') }}</p>
                    </div>
                </div>
            @endif

            {{-- Team or Image Section --}}
            <div class="relative rounded-2xl md:rounded-3xl overflow-hidden h-56 sm:h-72 md:h-96">
                @if(!empty($aboutImage))
                    <img src="{{ url('storage/' . $aboutImage) }}" alt="{{ __('About') }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-linear-to-br from-primary-500 to-primary-800"></div>
                @endif
                <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                    <h2 class="text-white text-lg sm:text-2xl md:text-3xl font-bold text-center px-4">
                        {{ !empty($aboutCta) ? $aboutCta : __('Built for Creators, by Creators') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
