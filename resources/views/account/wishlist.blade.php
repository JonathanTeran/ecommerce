<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-gray-900 dark:text-white mb-8">{{ __('Mi Wishlist') }}</h1>

            <div class="flex gap-4 mb-8 text-sm font-medium">
                <a href="{{ route('account.profile') }}"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">{{ __('Perfil') }}</a>
                <a href="{{ route('account.orders') }}"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">{{ __('Pedidos') }}</a>
                <a href="{{ route('account.addresses') }}"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">{{ __('Direcciones') }}</a>
                <a href="{{ route('account.wishlist') }}"
                    class="text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 pb-1">{{ __('Wishlist') }}</a>
            </div>

            @if ($wishlistItems->isEmpty())
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 dark:text-zinc-600 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('No tienes productos guardados aún.') }}</p>
                    <a href="{{ route('shop.index') }}"
                        class="inline-flex items-center mt-4 px-6 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition">
                        {{ __('Explorar tienda') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($wishlistItems as $item)
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-4">
                            @if ($item->product)
                                <div class="flex gap-4">
                                    <div
                                        class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
                                        @if ($item->product->image_url)
                                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                                                class="h-full w-full object-cover object-center">
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ $item->product->name }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            ${{ number_format((float) $item->product->price, 2) }}
                                        </p>
                                        <div class="mt-4">
                                            <a href="{{ route('products.show', $item->product->slug) }}"
                                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-zinc-700 transition">
                                                {{ __('Ver producto') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Este producto ya no está disponible.') }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
