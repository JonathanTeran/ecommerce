<x-layouts.app
    :title="$page->meta_title ?: $page->title"
    :metaDescription="$page->meta_description ?? ''"
>
    @forelse ($page->content ?? [] as $block)
        <x-dynamic-component
            :component="'page-blocks.' . str_replace('_', '-', $block['type'])"
            :data="$block['data']"
        />
    @empty
        <div class="flex items-center justify-center min-h-100 bg-gray-50 dark:bg-zinc-950">
            <div class="text-center py-20">
                <h1 class="text-3xl font-bold text-gray-500 dark:text-zinc-400">{{ $page->title }}</h1>
                <p class="text-gray-400 dark:text-zinc-600 mt-2">{{ __('Esta pagina aun no tiene contenido.') }}</p>
            </div>
        </div>
    @endforelse
</x-layouts.app>
