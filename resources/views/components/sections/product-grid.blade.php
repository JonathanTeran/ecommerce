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
        <h2 class="text-3xl md:text-4xl font-bold font-heading text-gray-900 dark:text-white mb-12">
            {{ __($config['heading'] ?? 'Tendencias') }}
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-10">
            <template x-for="product in products" :key="product.id">
                <div class="group relative">
                    <div
                        class="aspect-[3/4] w-full overflow-hidden rounded-2xl bg-gray-100 dark:bg-zinc-900 lg:aspect-none lg:h-96 relative">
                        <img :src="product.image_url || 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27400%27 height=%27600%27%3E%3Crect fill=%27%23f1f5f9%27 width=%27400%27 height=%27600%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 dominant-baseline=%27middle%27 text-anchor=%27middle%27 fill=%27%2394a3b8%27 font-size=%2718%27%3ENo Image%3C/text%3E%3C/svg%3E'"
                            :alt="product.name"
                            class="h-full w-full object-cover object-center lg:h-full lg:w-full transform group-hover:scale-110 transition duration-700">
                        <div
                            class="absolute inset-x-0 bottom-0 p-4 opacity-0 group-hover:opacity-100 transition duration-300 translate-y-4 group-hover:translate-y-0">
                            <button @click="$store.cart.add(product.id)"
                                class="w-full bg-white text-black font-bold py-3 rounded-xl shadow-lg hover:bg-gray-100 transition"
                                :disabled="$store.cart.loading">
                                <span x-show="!$store.cart.loading">{{ __('Agregar al Carrito') }}</span>
                                <span x-show="$store.cart.loading">{{ __('Agregando...') }}</span>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between">
                        <div>
                            <h3 class="text-sm text-gray-700 dark:text-gray-300">
                                <a :href="product.url">
                                    <span aria-hidden="true" class="absolute inset-0"></span>
                                    <span x-text="product.name"></span>
                                </a>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500" x-text="product.category_name"></p>
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white"
                            x-text="product.formatted_price"></p>
                    </div>
                </div>
            </template>
        </div>

        @if($enableInfiniteScroll)
            <div class="mt-16 text-center">
                <button x-show="hasMore" @click="loadMore()"
                    class="px-8 py-3 bg-black dark:bg-white text-white dark:text-black font-bold rounded-full hover:opacity-80 transition disabled:opacity-50"
                    :disabled="loading">
                    <span x-show="!loading">{{ __('Cargar Más Productos') }}</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        {{ __('Cargando...') }}
                    </span>
                </button>
                <p x-show="!hasMore" class="text-gray-500 mt-4">{{ __('Has llegado al final.') }}</p>
            </div>
        @endif
    </div>
</section>
