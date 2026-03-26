{{-- Magic Search --}}
@if($navConfig['show_search'] ?? true)
<div x-data="{
    searchOpen: false,
    query: '',
    results: [],
    isLoading: false,
    controller: null,
    async search() {
        if (this.query.length < 2) {
            this.results = [];
            return;
        }

        if (this.controller) {
            this.controller.abort();
        }
        this.controller = new AbortController();
        const signal = this.controller.signal;

        this.isLoading = true;
        try {
            const res = await fetch('/api/search?q=' + this.query, { signal });
            this.results = await res.json();
        } catch (e) {
            if (e.name !== 'AbortError') {
                console.error(e);
            }
        } finally {
            if (!signal.aborted) {
                this.isLoading = false;
            }
        }
    }
}" class="relative">
    <div class="flex items-center">
        <button @click="searchOpen = !searchOpen" x-show="!searchOpen"
            class="text-slate-600 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </button>

        <div x-show="searchOpen" x-transition.origin.right
            class="absolute right-0 top-1/2 -translate-y-1/2 w-72 sm:w-96 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-full shadow-lg flex items-center px-4 py-2 z-50">
            <svg class="w-5 h-5 text-slate-400 mr-2" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input x-model="query" @input.debounce.300ms="search()" @keydown.escape="searchOpen = false"
                type="text" placeholder="{{ __('Search premium products...') }}"
                class="w-full bg-transparent border-none focus:ring-0 text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 h-12"
                autofocus>

            {{-- Loading Indicator --}}
            <div x-show="isLoading" class="mr-2" x-transition>
                <svg class="animate-spin h-4 w-4 text-primary-500" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>

            {{-- Clear Button --}}
            <button x-show="query.length > 0"
                @click="query = ''; searchOpen = false; results = []; $nextTick(() => $el.previousElementSibling.previousElementSibling.focus())"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                class="mr-2 p-1 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all focus:outline-none focus:ring-2 focus:ring-primary-500/50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    {{-- Search Results Dropdown --}}
    <div x-show="searchOpen && (results.length > 0 || query.length >= 2)"
        @click.away="searchOpen = false" x-transition x-cloak
        class="absolute right-0 top-full mt-4 w-96 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden z-50">

        <div x-show="isLoading" class="p-4 text-center text-slate-500 text-sm">
            <svg class="animate-spin h-5 w-5 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            {{ __('Searching...') }}
        </div>

        <div x-show="!isLoading && results.length === 0 && query.length >= 2"
            class="p-4 text-center text-slate-500 text-sm">
            {{ __('No products found for') }} "<span x-text="query"
                class="font-medium text-slate-900 dark:text-slate-100"></span>"
        </div>

        <div x-show="!isLoading && results.length > 0">
            <div
                class="px-4 py-3 bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-100 dark:border-zinc-800">
                <span
                    class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Products') }}</span>
            </div>
            <ul class="divide-y divide-slate-100 dark:divide-zinc-800 max-h-96 overflow-y-auto">
                <template x-for="product in results" :key="product.id">
                    <li>
                        <a :href="product.url"
                            class="flex items-center gap-4 px-4 py-3 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors group">
                            <div
                                class="h-12 w-12 rounded-lg bg-slate-100 dark:bg-zinc-800 overflow-hidden flex-shrink-0">
                                <img :src="product.image_url" :alt="product.name"
                                    class="h-full w-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-white truncate group-hover:text-primary-600 transition-colors"
                                    x-text="product.name"></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400"
                                    x-text="product.category"></p>
                            </div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white"
                                x-text="product.formatted_price"></div>
                        </a>
                    </li>
                </template>
            </ul>
            <a :href="'{{ route('shop.index') }}?q=' + query"
                class="block text-center py-3 text-xs font-semibold text-primary-600 hover:bg-slate-50 dark:hover:bg-zinc-800 border-t border-slate-100 dark:border-slate-800 transition-colors">
                {{ __('View all results') }}
            </a>
        </div>
    </div>
</div>
@endif
