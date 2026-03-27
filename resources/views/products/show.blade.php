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
                <a href="{{ route('home') }}" class="hover:text-gray-900 dark:hover:text-white">{{ __('Inicio') }}</a>
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
                                {{ __('En Stock') }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                {{ __('Agotado') }}
                            </span>
                        @endif
                    </div>

                    <div class="prose dark:prose-invert mb-8 text-gray-600 dark:text-gray-300">
                        {!! $product->description !!}
                    </div>

                    {{-- Specifications --}}
                    @if (($tenantSettings?->getProductPageConfig()['show_specifications'] ?? true) && $product->specifications && count($product->specifications) > 0)
                        <div class="mb-8 p-4 bg-gray-50 dark:bg-zinc-900 rounded-xl">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">{{ __('Especificaciones') }}</h3>
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
                            <label class="font-medium text-gray-900 dark:text-white">{{ __('Cantidad') }}</label>
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
                                {{ $product->is_in_stock ? __('Agregar al Carrito') : __('Agotado') }}
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
                                {{ __('Agregando...') }}
                            </span>
                        </button>
                    </div>

                    {{-- Additional Details --}}
                    <div class="mt-8 space-y-3 text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900 dark:text-white w-20">{{ __('Código') }}:</span>
                            <span
                                class="font-mono bg-gray-100 dark:bg-zinc-800 px-2 py-1 rounded text-xs">{{ $product->sku }}</span>
                        </div>
                        @if ($product->category)
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900 dark:text-white w-20">{{ __('Categoría') }}:</span>
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
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('También te puede interesar') }}</h2>
                        <a href="{{ route('shop.index', ['category' => $product->category?->slug]) }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1">
                            {{ __('Ver todos') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                        @foreach ($relatedProducts as $related)
                            @php
                                $relDiscount = ($related->compare_price && $related->compare_price > $related->price)
                                    ? round(($related->compare_price - $related->price) / $related->compare_price * 100)
                                    : null;
                                $relRating = $related->reviews->avg('rating');
                                $relReviewsCount = $related->reviews->count();
                            @endphp
                            <div class="group relative bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden hover:shadow-xl hover:shadow-black/5 dark:hover:shadow-black/30 transition-all duration-300 hover:-translate-y-1">
                                {{-- Image --}}
                                <div class="relative aspect-square overflow-hidden bg-gray-50 dark:bg-zinc-800">
                                    <a href="{{ route('products.show', $related) }}">
                                        <img src="{{ $related->image_url ?: 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27400%27 height=%27400%27%3E%3Crect fill=%27%23f1f5f9%27 width=%27400%27 height=%27400%27/%3E%3C/svg%3E' }}"
                                            alt="{{ $related->name }}"
                                            class="h-full w-full object-cover group-hover:scale-105 transition duration-500">
                                    </a>
                                    {{-- Badges --}}
                                    <div class="absolute top-2 left-2 flex flex-col gap-1.5">
                                        @if($relDiscount)
                                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold bg-red-500 text-white shadow-sm">-{{ $relDiscount }}%</span>
                                        @elseif($related->is_new)
                                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500 text-white shadow-sm">{{ __('NUEVO') }}</span>
                                        @elseif($related->is_featured)
                                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500 text-white shadow-sm">{{ __('TOP') }}</span>
                                        @endif
                                    </div>
                                    {{-- Quick Add --}}
                                    <button @click.prevent="$store.cart.add({{ $related->id }})"
                                        class="absolute bottom-3 right-3 bg-indigo-600 text-white p-2.5 rounded-xl shadow-lg opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0 hover:bg-indigo-700 z-10">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                    </button>
                                </div>
                                {{-- Info --}}
                                <div class="p-3 md:p-4">
                                    @if($related->brand)
                                        <p class="text-[10px] md:text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-1">{{ $related->brand->name }}</p>
                                    @endif
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight line-clamp-2 mb-2">
                                        <a href="{{ route('products.show', $related) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                            {{ $related->name }}
                                        </a>
                                    </h3>
                                    @if($relReviewsCount > 0)
                                        <div class="flex items-center gap-1 mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-3.5 h-3.5 {{ $i <= round($relRating) ? 'text-amber-400' : 'text-gray-200 dark:text-zinc-700' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                            <span class="text-xs text-gray-400 ml-1">({{ $relReviewsCount }})</span>
                                        </div>
                                    @endif
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $related->formatted_price }}</span>
                                        @if($related->compare_price && $related->compare_price > $related->price)
                                            <span class="text-sm text-gray-400 line-through">${{ number_format($related->compare_price, 2) }}</span>
                                        @endif
                                    </div>
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
                {{ $product->is_in_stock ? __('Agregar al Carrito') : __('Agotado') }}
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
