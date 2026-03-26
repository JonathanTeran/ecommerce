<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-12" x-data="{ search: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h1 class="text-4xl md:text-5xl font-bold font-heading text-gray-900 dark:text-white mb-6">
                    {{ __('Nuestros Productos') }}
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mb-8">
                    {{ __('Explora nuestra amplia variedad de categorías y encuentra lo que necesitas.') }}
                </p>

                {{-- Search Bar --}}
                <div class="max-w-md mx-auto relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary-500 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" x-model="search" placeholder="{{ __('Buscar categoría...') }}"
                        class="block w-full pl-10 pr-3 py-4 border border-gray-200 dark:border-zinc-800 rounded-full leading-5 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm shadow-sm transition-shadow duration-300 focus:shadow-lg">
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($categories as $category)
                    <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
                        x-show="search === '' || '{{ strtolower($category->name) }}'.includes(search.toLowerCase())"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                        class="group relative bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-sm hover:shadow-2xl hover:shadow-primary-500/20 transition-all duration-300 transform hover:-translate-y-2 border border-gray-100 dark:border-zinc-800 flex flex-col items-center text-center overflow-hidden h-full">

                        {{-- Icon/Image Container --}}
                        <div
                            class="w-32 h-32 mb-6 relative flex items-center justify-center p-4 rounded-full bg-primary-50 dark:bg-primary-900/20 group-hover:bg-primary-100 dark:group-hover:bg-primary-900/40 transition-colors duration-300 shrink-0">
                            @if ($category->hasMedia('image'))
                                <img src="{{ $category->getFirstMediaUrl('image') }}" alt="{{ $category->name }}"
                                    class="w-full h-full object-contain transform group-hover:scale-110 transition-transform duration-300">
                            @else
                                {{-- Fallback Icon --}}
                                <svg class="w-16 h-16 text-primary-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                    </path>
                                </svg>
                            @endif
                        </div>

                        {{-- Name --}}
                        <h3
                            class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-primary-600 transition-colors line-clamp-2">
                            {{ $category->name }}
                        </h3>

                        {{-- Subtitle/Count --}}
                        <p
                            class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors mt-auto">
                            {{ $category->all_products_count ?? 0 }} {{ __('Artículos') }}
                        </p>

                        {{-- Decorative background gradient --}}
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-transparent to-primary-50/30 dark:to-primary-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- No Results Message --}}
            <div x-show="search !== '' && $el.closest('.max-w-7xl').querySelectorAll('a[x-show]').length > 0 && Array.from($el.closest('.max-w-7xl').querySelectorAll('a[x-show]')).every(el => el.style.display === 'none')"
                class="text-center py-12" style="display: none;">
                <p class="text-gray-500 dark:text-gray-400">
                    {{ __('No se encontraron categorías que coincidan con tu búsqueda.') }}</p>
                <button @click="search = ''"
                    class="text-primary-600 font-bold hover:underline mt-2">{{ __('Limpiar búsqueda') }}</button>
            </div>
        </div>
    </div>
</x-layouts.app>
