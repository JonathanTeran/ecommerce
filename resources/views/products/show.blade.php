<x-layouts.app
    :title="$seo['title'] ?? $product->name"
    :metaDescription="$seo['metaDescription'] ?? ''"
    :ogImage="$seo['ogImage'] ?? ''"
    :ogType="$seo['ogType'] ?? 'product'"
    :canonical="$seo['canonical'] ?? ''"
    :jsonLd="$seo['jsonLd'] ?? null"
>
    <div class="bg-white dark:bg-zinc-950 min-h-screen pt-20">
        <div class="container mx-auto px-4 py-8">
            {{-- Breadcrumbs --}}
            <nav class="flex flex-wrap mb-6 md:mb-8 text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-gray-900 dark:hover:text-white">{{ __('Home') }}</a>
                <span class="mx-2">/</span>
                <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}"
                    class="hover:text-gray-900 dark:hover:text-white">{{ $product->category->name }}</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 dark:text-white font-medium">{{ $product->name }}</span>
            </nav>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-12">
                {{-- Gallery with Zoom --}}
                <div class="space-y-4" x-data="{
                    activeImage: '{{ $product->image_url ?: 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27800%27 height=%27600%27%3E%3Crect fill=%27%23f1f5f9%27 width=%27800%27 height=%27600%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 dominant-baseline=%27middle%27 text-anchor=%27middle%27 fill=%27%2394a3b8%27 font-size=%2724%27%3ENo Image%3C/text%3E%3C/svg%3E' }}',
                    zoom: false,
                    x: 0,
                    y: 0,
                    handleMouseOver(e) {
                        this.zoom = true;
                    },
                    handleMouseOut() {
                        this.zoom = false;
                    },
                    handleMouseMove(e) {
                        const { left, top, width, height } = e.target.getBoundingClientRect();
                        this.x = ((e.clientX - left) / width) * 100;
                        this.y = ((e.clientY - top) / height) * 100;
                    }
                }">
                    <div class="relative aspect-square bg-white dark:bg-zinc-900 rounded-2xl overflow-hidden border border-gray-100 dark:border-zinc-800"
                        @mouseenter="handleMouseOver" @mouseleave="handleMouseOut" @mousemove="handleMouseMove">

                        <img :src="activeImage" alt="{{ $product->name }}"
                            class="w-full h-full object-contain p-4 transition-opacity duration-300"
                            :class="{ 'opacity-0': zoom, 'opacity-100': !zoom }">

                        <div class="absolute inset-0 pointer-events-none bg-no-repeat transition-opacity duration-300"
                            :style="`background-image: url('${activeImage}'); background-position: ${x}% ${y}%; background-size: 200%;`"
                            :class="{ 'opacity-100': zoom, 'opacity-0': !zoom }">
                        </div>
                    </div>

                    @if ($product->getMedia('images')->count() > 1)
                        <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                            @foreach ($product->getMedia('images') as $media)
                                <button @click="activeImage = '{{ $media->getUrl() }}'"
                                    class="aspect-square bg-white dark:bg-zinc-900 rounded-lg overflow-hidden border-2 transition p-1"
                                    :class="activeImage === '{{ $media->getUrl() }}' ?
                                        'border-primary-600 dark:border-primary-400' :
                                        'border-transparent hover:border-gray-300 dark:hover:border-zinc-700'">
                                    <img src="{{ $media->getUrl('thumb') }}" class="w-full h-full object-contain">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Product Info --}}
                <div>
                    @if ($product->brand)
                        <a href="{{ route('shop.index', ['brand' => $product->brand->slug]) }}"
                            class="text-sm font-medium text-indigo-600 dark:text-indigo-400 mb-2 block hover:underline">
                            {{ $product->brand->name }}
                        </a>
                    @endif

                    <div class="flex justify-between items-start mb-4">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white leading-tight">
                            {{ $product->name }}
                        </h1>
                        <livewire:wishlist-button :product="$product" />
                    </div>

                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 mb-6">
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{{ $product->formatted_price }}</p>
                        @if ($product->compare_price > $product->price)
                            <p class="text-xl text-gray-500 line-through">
                                {{ '$' . number_format($product->compare_price, 2) }}</p>
                        @endif

                        @if ($product->is_in_stock)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                In Stock
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                {{ __('Out of Stock') }}
                            </span>
                        @endif
                    </div>

                    <div class="prose dark:prose-invert mb-8 text-gray-600 dark:text-gray-300">
                        {!! $product->description !!}
                    </div>

                    {{-- Specifications --}}
                    @if (($tenantSettings?->getProductPageConfig()['show_specifications'] ?? true) && $product->specifications && count($product->specifications) > 0)
                        <div class="mb-8 p-4 bg-gray-50 dark:bg-zinc-900 rounded-xl">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">{{ __('Specifications') }}</h3>
                            <div class="grid grid-cols-1 gap-y-2">
                                @foreach ($product->specifications as $key => $value)
                                    <div
                                        class="flex justify-between border-b border-gray-200 dark:border-zinc-800 last:border-0 pb-2 last:pb-0">
                                        <span class="text-gray-600 dark:text-gray-400">{{ ucfirst($key) }}</span>
                                        <span
                                            class="font-medium text-gray-900 dark:text-white text-right">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Add to Cart --}}
                    <div class="border-t border-gray-200 dark:border-zinc-800 pt-8 hidden md:block"
                        x-data="{ quantity: 1 }">
                        <div class="flex items-center gap-6 mb-6">
                            <label class="font-medium text-gray-900 dark:text-white">{{ __('Quantity') }}</label>
                            <div
                                class="flex items-center border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900">
                                <button @click="quantity > 1 ? quantity-- : null"
                                    class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">-</button>
                                <span class="px-3 py-2 text-gray-900 dark:text-white w-10 text-center font-medium"
                                    x-text="quantity">1</span>
                                <button @click="quantity++"
                                    class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">+</button>
                            </div>
                        </div>

                        <button @click="$store.cart.add({{ $product->id }}, quantity)"
                            :disabled="!{{ $product->is_in_stock ? 'true' : 'false' }} || $store.cart.loading"
                            class="w-full bg-indigo-600 dark:bg-indigo-500 text-white font-bold py-4 rounded-full hover:bg-indigo-700 dark:hover:bg-indigo-600 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-indigo-200 dark:shadow-none">
                            <span x-show="!$store.cart.loading">
                                {{ $product->is_in_stock ? __('Add to Cart') : __('Out of Stock') }}
                            </span>
                            <span x-show="$store.cart.loading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                {{ __('Adding...') }}
                            </span>
                        </button>
                    </div>

                    {{-- Additional Details --}}
                    <div class="mt-8 space-y-3 text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900 dark:text-white w-20">{{ __('SKU') }}:</span>
                            <span
                                class="font-mono bg-gray-100 dark:bg-zinc-800 px-2 py-1 rounded text-xs">{{ $product->sku }}</span>
                        </div>
                        @if ($product->category)
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900 dark:text-white w-20">{{ __('Category') }}:</span>
                                <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}"
                                    class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $product->category->name }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Reviews Section --}}
            @if($tenantSettings?->getProductPageConfig()['show_reviews'] ?? true)
            <livewire:product-reviews :product="$product" />
            @endif

            {{-- Related Products --}}
            @if (($tenantSettings?->getProductPageConfig()['show_related_products'] ?? true) && $relatedProducts->count() > 0)
                <div class="mt-20">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">{{ __('You may also like') }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                        @foreach ($relatedProducts as $related)
                            <div class="group relative">
                                <div
                                    class="aspect-[3/4] w-full overflow-hidden rounded-xl bg-gray-100 dark:bg-zinc-900 relative">
                                    <img src="{{ $related->image_url ?: 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27400%27 height=%27500%27%3E%3Crect fill=%27%23f1f5f9%27 width=%27400%27 height=%27500%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 dominant-baseline=%27middle%27 text-anchor=%27middle%27 fill=%27%2394a3b8%27 font-size=%2718%27%3ENo Image%3C/text%3E%3C/svg%3E' }}"
                                        alt="{{ $related->name }}"
                                        class="h-full w-full object-cover object-center group-hover:scale-105 transition duration-300">
                                </div>
                                <div class="mt-4 flex justify-between">
                                    <div>
                                        <h3 class="text-sm text-gray-700 dark:text-gray-300">
                                            <a href="{{ route('products.show', $related) }}">
                                                <span aria-hidden="true" class="absolute inset-0"></span>
                                                {{ $related->name }}
                                            </a>
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $related->category->name }}</p>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $related->formatted_price }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Mobile Sticky Add to Cart --}}
    <div
        class="fixed bottom-0 left-0 right-0 bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800 p-4 md:hidden z-50 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $product->name }}</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $product->formatted_price }}</p>
            </div>
            <button @click="$store.cart.add({{ $product->id }}, 1)"
                :disabled="!{{ $product->is_in_stock ? 'true' : 'false' }} || $store.cart.loading"
                class="px-6 py-3 bg-indigo-600 dark:bg-indigo-500 text-white font-bold rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                {{ $product->is_in_stock ? __('Add to Cart') : __('Out of Stock') }}
            </button>
        </div>
    </div>
    {{-- Ecommerce Tracking: view_item --}}
    @push('head')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.trackEcommerce === 'function') {
                window.trackEcommerce('view_item', {
                    value: {{ $product->price }},
                    items: [{
                        item_id: {{ $product->id }},
                        item_name: @json($product->name),
                        price: {{ $product->price }},
                        item_category: @json($product->category?->name ?? ''),
                        item_brand: @json($product->brand?->name ?? ''),
                        quantity: 1
                    }]
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>
