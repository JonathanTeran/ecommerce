<x-layouts.app :title="$title ?? __('Tienda')" :metaDescription="$metaDescription ?? ''">
    <div class="bg-white dark:bg-zinc-950 min-h-screen pt-20">
        <div class="container mx-auto px-4 py-8">
            {{-- Banners Section --}}
            @if ($banners->isNotEmpty())
                <div class="mb-12 rounded-2xl overflow-hidden shadow-xl" x-data="{
                    activeSlide: 0,
                    slides: {{ $banners->map(
                            fn($b) => [
                                'id' => $b->id,
                                'image' => $b->getFirstMediaUrl('image'),
                                'title' => $b->title,
                                'subtitle' => $b->subtitle,
                                'link' => $b->button_url,
                                'button_text' => $b->button_text,
                            ],
                        )->toJson() }},
                    loop() {
                        setInterval(() => { this.activeSlide = this.activeSlide === this.slides.length - 1 ? 0 : this.activeSlide + 1 }, 5000)
                    }
                }" x-init="loop()">
                    <div class="relative h-[250px] sm:h-[300px] md:h-[400px] w-full">
                        <template x-for="(slide, index) in slides" :key="slide.id">
                            <div x-show="activeSlide === index"
                                x-transition:enter="transition transform duration-700 ease-in-out"
                                x-transition:enter-start="opacity-0 translate-x-10"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition transform duration-700 ease-in-out"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 -translate-x-10"
                                class="absolute inset-0 w-full h-full bg-gray-900">
                                <img :src="slide.image"
                                    class="absolute inset-0 w-full h-full object-cover opacity-60">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent">
                                </div>

                                <div class="absolute inset-0 flex items-center justify-center text-center px-4">
                                    <div class="max-w-3xl space-y-6">
                                        <h2 x-text="slide.title"
                                            class="text-xl sm:text-3xl md:text-4xl lg:text-6xl font-black text-white tracking-tight drop-shadow-lg">
                                        </h2>
                                        <p x-show="slide.subtitle" x-text="slide.subtitle"
                                            class="text-sm sm:text-base md:text-xl text-gray-200 font-medium drop-shadow-md"></p>

                                        <div x-show="slide.link" class="pt-4">
                                            <a :href="slide.link"
                                                x-text="slide.button_text || '{{ __('Comprar Ahora') }}'"
                                                class="inline-block bg-white text-black px-6 py-2.5 sm:px-8 sm:py-3 rounded-full text-sm sm:text-base font-bold hover:bg-gray-100 hover:scale-105 transition transform shadow-xl">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Indicators --}}
                        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-3 z-20">
                            <template x-for="(slide, index) in slides" :key="index">
                                <button @click="activeSlide = index"
                                    class="w-3 h-3 rounded-full transition-all duration-300"
                                    :class="activeSlide === index ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/80'">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            @endif

            @php
                $shopConfig = $tenantSettings?->getShopPageConfig() ?? [];
                $showCategoryFilter = $shopConfig['show_category_filter'] ?? true;
                $showBrandFilter = $shopConfig['show_brand_filter'] ?? true;
                $shopGridCols = $shopConfig['grid_columns'] ?? 3;
                $showSidebar = $showCategoryFilter || $showBrandFilter;
            @endphp
            <div class="flex flex-col lg:flex-row gap-8">
                {{-- Sidebar Filters --}}
                @if($showSidebar)
                <div class="w-full lg:w-1/4 space-y-6">
                    {{-- Categories Filter --}}
                    @if($showCategoryFilter)
                    <div x-data="{ expanded: {{ request('category') ? 'true' : 'false' }} }" class="border-b border-gray-200 dark:border-zinc-800 pb-6">
                        <button @click="expanded = !expanded"
                            class="flex items-center justify-between w-full py-2 text-left group">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ __('Categorías') }}</h3>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200"
                                :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="expanded" x-collapse style="display: none;"> {{-- x-collapse requires Alpine Collapse plugin, falling back to simple show if not available, verify plugin --}}
                            <ul class="space-y-3 mt-4">
                                <li>
                                    <a href="{{ route('shop.index', array_merge(request()->except('category', 'page'))) }}"
                                        class="flex items-center gap-3 text-sm group">
                                        <div
                                            class="w-5 h-5 rounded border flex items-center justify-center transition-colors {{ !request('category') ? 'bg-black border-black dark:bg-white dark:border-white' : 'border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 group-hover:border-gray-400' }}">
                                            @if (!request('category'))
                                                <svg class="w-3.5 h-3.5 text-white dark:text-black" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <span
                                            class="{{ !request('category') ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200' }}">
                                            {{ __('Todas') }}
                                        </span>
                                    </a>
                                </li>
                                @foreach ($categories as $category)
                                    @php $isActive = request('category') == $category->slug; @endphp
                                    <li>
                                        <a href="{{ route('shop.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                                            class="flex items-center gap-3 text-sm group">
                                            <div
                                                class="w-5 h-5 rounded border flex items-center justify-center transition-colors {{ $isActive ? 'bg-black border-black dark:bg-white dark:border-white' : 'border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 group-hover:border-gray-400' }}">
                                                @if ($isActive)
                                                    <svg class="w-3.5 h-3.5 text-white dark:text-black" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                            <span
                                                class="{{ $isActive ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200' }}">
                                                {{ $category->name }}
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    @endif

                    {{-- Price Range Filter --}}
                    <div x-data="{ expanded: {{ (request('min_price') || request('max_price')) ? 'true' : 'false' }}, minPrice: '{{ request('min_price', '') }}', maxPrice: '{{ request('max_price', '') }}' }" class="border-b border-gray-200 dark:border-zinc-800 pb-6">
                        <button @click="expanded = !expanded"
                            class="flex items-center justify-between w-full py-2 text-left group">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ __('Precio') }}</h3>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200"
                                :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="expanded" x-collapse>
                            <form method="GET" action="{{ route('shop.index') }}" class="mt-4 space-y-3">
                                @foreach(request()->except(['min_price', 'max_price', 'page']) as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <div class="flex items-center gap-2">
                                    <input type="number" name="min_price" x-model="minPrice" placeholder="{{ __('Mín') }}" min="0" step="0.01"
                                        class="w-full text-sm border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 dark:text-white px-3 py-2">
                                    <span class="text-gray-400">—</span>
                                    <input type="number" name="max_price" x-model="maxPrice" placeholder="{{ __('Máx') }}" min="0" step="0.01"
                                        class="w-full text-sm border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 dark:text-white px-3 py-2">
                                </div>
                                <button type="submit"
                                    class="w-full text-sm font-medium bg-indigo-600 text-white rounded-lg px-4 py-2 hover:bg-indigo-700 transition">
                                    {{ __('Aplicar') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Brands Filter --}}
                    @if($showBrandFilter)
                    <div x-data="{ expanded: {{ request('brand') ? 'true' : 'false' }} }" class="border-b border-gray-200 dark:border-zinc-800 pb-6">
                        <button @click="expanded = !expanded"
                            class="flex items-center justify-between w-full py-2 text-left group">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ __('Marcas') }}</h3>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200"
                                :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="expanded" x-collapse>
                            <ul class="space-y-3 mt-4">
                                @foreach ($brands as $brand)
                                    @php $isActive = request('brand') == $brand->slug; @endphp
                                    <li>
                                        <a href="{{ route('shop.index', array_merge(request()->except('page'), ['brand' => $isActive ? null : $brand->slug])) }}"
                                            class="flex items-center gap-3 text-sm group">
                                            <div
                                                class="w-5 h-5 rounded border flex items-center justify-center transition-colors {{ $isActive ? 'bg-black border-black dark:bg-white dark:border-white' : 'border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 group-hover:border-gray-400' }}">
                                                @if ($isActive)
                                                    <svg class="w-3.5 h-3.5 text-white dark:text-black" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                            <span
                                                class="{{ $isActive ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200' }}">
                                                {{ $brand->name }}
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Product Grid --}}
                <div class="w-full {{ $showSidebar ? 'lg:w-3/4' : '' }}">
                    {{-- Header / Sort --}}
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ __('Tienda') }}</h1>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500 hidden sm:inline">{{ __('Ordenar por:') }}</span>
                            <select onchange="window.location.href=this.value"
                                class="text-sm border-gray-300 rounded-md dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
                                <option
                                    value="{{ route('shop.index', array_merge(request()->query(), ['sort' => 'latest'])) }}"
                                    {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('Más recientes') }}</option>
                                <option
                                    value="{{ route('shop.index', array_merge(request()->query(), ['sort' => 'price_asc'])) }}"
                                    {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                    {{ __('Precio: Menor a Mayor') }}</option>
                                <option
                                    value="{{ route('shop.index', array_merge(request()->query(), ['sort' => 'price_desc'])) }}"
                                    {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                    {{ __('Precio: Mayor a Menor') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Grid --}}
                    <div class="grid grid-cols-2 gap-3 sm:gap-6 {{ match((int) $shopGridCols) { 2 => 'md:grid-cols-2', 4 => 'md:grid-cols-3 lg:grid-cols-4', default => 'md:grid-cols-3' } }}">
                        @forelse($products as $product)
                            @php
                                $discount = ($product->compare_price && $product->compare_price > $product->price)
                                    ? round(($product->compare_price - $product->price) / $product->compare_price * 100)
                                    : null;
                                $avgRating = $product->reviews()->avg('rating');
                                $reviewsCount = $product->reviews()->count();
                            @endphp
                            <div
                                class="group relative bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
                                <div class="aspect-square w-full overflow-hidden bg-gray-50 dark:bg-zinc-800 relative">
                                    <a href="{{ route('products.show', $product) }}">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                            class="h-full w-full object-cover group-hover:scale-105 transition duration-500">
                                    </a>
                                    {{-- Badges --}}
                                    <div class="absolute top-2 left-2 flex flex-col gap-1.5">
                                        @if($discount)
                                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold bg-red-500 text-white shadow-sm">-{{ $discount }}%</span>
                                        @elseif($product->is_new)
                                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500 text-white shadow-sm">NUEVO</span>
                                        @elseif($product->is_featured)
                                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500 text-white shadow-sm">TOP</span>
                                        @endif
                                    </div>
                                    {{-- Quick Add --}}
                                    <button @click="$store.cart.add({{ $product->id }})"
                                        class="absolute bottom-3 right-3 bg-indigo-600 text-white p-2.5 rounded-xl shadow-lg opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0 hover:bg-indigo-700 z-10">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="p-3 md:p-4 flex flex-col flex-grow justify-between">
                                    <div>
                                        @if($product->brand)
                                            <p class="text-[10px] md:text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-1">{{ $product->brand->name }}</p>
                                        @endif
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight line-clamp-2 mb-2">
                                            <a href="{{ route('products.show', $product) }}" class="hover:text-indigo-600 transition-colors">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                        @if($reviewsCount > 0)
                                            <div class="flex items-center gap-1 mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-3.5 h-3.5 {{ $i <= round($avgRating) ? 'text-amber-400' : 'text-gray-200 dark:text-zinc-700' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                @endfor
                                                <span class="text-xs text-gray-400 ml-1">({{ $reviewsCount }})</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $product->formatted_price }}</span>
                                        @if($product->compare_price && $product->compare_price > $product->price)
                                            <span class="text-sm text-gray-400 line-through">${{ number_format($product->compare_price, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <p class="text-gray-500">{{ __('No se encontraron productos.') }}</p>
                                <a href="{{ route('shop.index') }}"
                                    class="text-indigo-600 hover:underline mt-2 inline-block">{{ __('Limpiar filtros') }}</a>
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
