@props(['section', 'data' => []])
@php
    $config = $section->config;
    $products = $data['products'] ?? collect();
    $enableInfiniteScroll = $config['enable_infinite_scroll'] ?? true;
    $style = $config['style'] ?? [];
    $customCss = \App\Support\SectionStyleHelper::toInlineCss($style);
    $hasCustomStyle = $customCss !== '';
@endphp

<section class="{{ $hasCustomStyle ? '' : 'py-20 bg-white dark:bg-zinc-950' }}"
    @if($hasCustomStyle) style="{!! $customCss !!}" @endif
    x-data="{
    products: {{ $products instanceof \Illuminate\Support\Collection ? $products->toJson() : json_encode($products) }},
    page: 1,
    hasMore: {{ $enableInfiniteScroll ? 'true' : 'false' }},
    loading: false,
    async loadMore() {
        if (this.loading || !this.hasMore) return;
        this.loading = true;
        this.page++;
        try {
            const res = await fetch(`/api/products?page=${this.page}`);
            const data = await res.json();
            if (data.data.length === 0) {
                this.hasMore = false;
            } else {
                this.products = [...this.products, ...data.data];
            }
        } catch (e) {
            console.error(e);
        } finally {
            this.loading = false;
        }
    }
}">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold font-heading mb-10"
            style="color: {{ $style['text_color'] ?? '#111827' }};">
            {{ __($config['heading'] ?? 'Tendencias') }}
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            <template x-for="product in products" :key="product.id">
                <div class="group relative bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden hover:shadow-xl hover:shadow-black/5 dark:hover:shadow-black/30 transition-all duration-300 hover:-translate-y-1">
                    {{-- Image Container --}}
                    <div class="relative aspect-square overflow-hidden bg-gray-50 dark:bg-zinc-800">
                        <a :href="product.url">
                            <img :src="product.image_url || 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27400%27 height=%27400%27%3E%3Crect fill=%27%23f1f5f9%27 width=%27400%27 height=%27400%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 dominant-baseline=%27middle%27 text-anchor=%27middle%27 fill=%27%2394a3b8%27 font-size=%2718%27%3ENo Image%3C/text%3E%3C/svg%3E'"
                                :alt="product.name"
                                class="h-full w-full object-cover transform group-hover:scale-105 transition duration-500">
                        </a>

                        {{-- Badges --}}
                        <div class="absolute top-2 left-2 flex flex-col gap-1.5">
                            <span x-show="product.discount_percent"
                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-red-500 text-white shadow-sm">
                                -<span x-text="product.discount_percent"></span>%
                            </span>
                            <span x-show="product.is_new && !product.discount_percent"
                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500 text-white shadow-sm">
                                NUEVO
                            </span>
                            <span x-show="product.is_featured && !product.is_new && !product.discount_percent"
                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500 text-white shadow-sm">
                                TOP
                            </span>
                        </div>

                        {{-- Low stock warning --}}
                        <div x-show="product.low_stock" class="absolute top-2 right-2">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                <span class="hidden sm:inline">Pocas unidades</span>
                            </span>
                        </div>
                    </div>

                    {{-- Product Info --}}
                    <div class="p-3 md:p-4">
                        {{-- Brand --}}
                        <p x-show="product.brand_name" class="text-[10px] md:text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-1" x-text="product.brand_name"></p>

                        {{-- Name --}}
                        <h3 class="text-sm md:text-base font-semibold text-gray-900 dark:text-white leading-tight line-clamp-2 mb-2">
                            <a :href="product.url" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span x-text="product.name"></span>
                            </a>
                        </h3>

                        {{-- Rating Stars --}}
                        <div x-show="product.reviews_count > 0" class="flex items-center gap-1.5 mb-2">
                            <div class="flex items-center">
                                <template x-for="i in 5" :key="'star-'+product.id+'-'+i">
                                    <svg class="w-3.5 h-3.5" :class="i <= Math.round(product.rating) ? 'text-amber-400' : 'text-gray-200 dark:text-zinc-700'" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                </template>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">(<span x-text="product.reviews_count"></span>)</span>
                        </div>

                        {{-- Price --}}
                        <div class="flex items-baseline gap-2 mb-3">
                            <span class="text-lg md:text-xl font-bold text-gray-900 dark:text-white" x-text="product.formatted_price"></span>
                            <span x-show="product.formatted_compare_price"
                                class="text-sm text-gray-400 line-through" x-text="product.formatted_compare_price"></span>
                        </div>

                        {{-- Add to Cart Button --}}
                        <button @click.prevent="$store.cart.add(product.id)"
                            class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-all duration-200 active:scale-95"
                            :disabled="$store.cart.loading || !product.in_stock">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path>
                            </svg>
                            <span x-show="product.in_stock && !$store.cart.loading">{{ __('Agregar') }}</span>
                            <span x-show="!product.in_stock">{{ __('Agotado') }}</span>
                            <span x-show="$store.cart.loading">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        @if($enableInfiniteScroll)
            <div class="mt-12 text-center">
                <button x-show="hasMore" @click="loadMore()"
                    class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-full hover:bg-indigo-700 transition disabled:opacity-50"
                    :disabled="loading">
                    <span x-show="!loading">{{ __('Ver Más Productos') }}</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('Cargando...') }}
                    </span>
                </button>
            </div>
        @endif
    </div>
</section>
