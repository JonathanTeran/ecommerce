@php
    $title = $config['title'] ?? '';
    $subtitle = $config['subtitle'] ?? '';
    $metaTitle = $config['meta_title'] ?? '';
    $metaDescription = $config['meta_description'] ?? '';
    $columns = (int) ($config['columns'] ?? 6);
    $showProductCount = $config['show_product_count'] ?? false;

    $gridClass = match ($columns) {
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
        default => 'grid-cols-2 md:grid-cols-4 lg:grid-cols-6',
    };
@endphp

<x-layouts.app :title="$metaTitle ?: ($title ?: __('Our Brands'))" :metaDescription="$metaDescription">
    <div class="bg-white dark:bg-zinc-950 pt-24 pb-12">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold font-heading text-slate-900 dark:text-white mb-8 text-center">
                {{ $title ?: __('Our Brands') }}
            </h1>
            <p class="text-slate-600 dark:text-slate-400 text-center max-w-2xl mx-auto mb-12">
                {{ $subtitle ?: __('Explore our curated selection of top-tier brands. Click on any brand to view its products.') }}
            </p>

            <div class="grid {{ $gridClass }} gap-6">
                @foreach ($brands as $brand)
                    <a href="{{ route('shop.index', ['brand' => $brand->slug]) }}"
                        class="group block bg-slate-50 dark:bg-zinc-900 rounded-xl p-8 flex items-center justify-center hover:shadow-lg transition duration-300 border border-transparent hover:border-primary-500/30 {{ $columns <= 4 ? 'h-40' : 'h-32' }}">
                        <div class="text-center">
                            @if ($brand->logo_url)
                                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                                    class="max-h-16 w-auto mx-auto grayscale group-hover:grayscale-0 transition duration-300 opacity-60 group-hover:opacity-100 object-contain">
                            @else
                                <span
                                    class="text-xl font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary-600 transition">{{ $brand->name }}</span>
                            @endif

                            @if ($showProductCount && isset($brand->products_count))
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $brand->products_count }} {{ trans_choice('product|products', $brand->products_count) }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($brands->isEmpty())
                <div class="text-center py-12">
                    <p class="text-slate-500 dark:text-slate-400 text-lg">{{ __('No brands available yet.') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
