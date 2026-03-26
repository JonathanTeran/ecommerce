<x-layouts.app
    :title="$seo['title'] ?? null"
    :metaDescription="$seo['metaDescription'] ?? null"
>
    @if(!empty($sectionFonts))
        @push('head')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="{{ \App\Support\SectionStyleHelper::googleFontsUrl($sectionFonts) }}" rel="stylesheet">
        @endpush
    @endif

    @forelse ($sections as $section)
        <x-dynamic-component
            :component="$section->type->bladeComponent()"
            :section="$section"
            :data="$sectionData[$section->id] ?? []"
        />
    @empty
        <div class="flex items-center justify-center min-h-100 bg-gray-50 dark:bg-zinc-950">
            <div class="text-center">
                <svg class="w-16 h-16 text-gray-300 dark:text-zinc-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <h2 class="text-xl font-semibold text-gray-500 dark:text-zinc-500">{{ __('Bienvenido a nuestra tienda') }}</h2>
                <p class="text-gray-400 dark:text-zinc-600 mt-2">{{ __('Pronto tendremos contenido aquí.') }}</p>
            </div>
        </div>
    @endforelse
</x-layouts.app>
