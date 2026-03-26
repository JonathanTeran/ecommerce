{{-- Cart Slide-over (Teleported to Body) --}}
<template x-teleport="body">
    <div x-show="$store.cart.open" x-cloak class="relative z-[99]" aria-labelledby="slide-over-title"
        role="dialog" aria-modal="true">
        <div x-show="$store.cart.open" x-transition:enter="ease-in-out duration-500"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity">
        </div>
        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="$store.cart.open"
                        x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                        class="pointer-events-auto w-screen max-w-md">
                        <div class="flex h-full flex-col overflow-y-scroll bg-white dark:bg-zinc-900 shadow-xl">
                            <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white"
                                        id="slide-over-title">
                                        {{ __('Shopping Cart') }}</h2>
                                    <div class="ml-3 flex h-7 items-center">
                                        <button @click="$store.cart.toggle()" type="button"
                                            class="relative -m-2 p-2 text-gray-400 hover:text-gray-500">
                                            <span class="absolute -inset-0.5"></span>
                                            <span class="sr-only">Close panel</span>
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-8">
                                    <div class="flow-root">
                                        <ul role="list"
                                            class="-my-6 divide-y divide-gray-200 dark:divide-zinc-800">
                                            <template x-for="item in $store.cart.items" :key="item.product_id">
                                                <li class="flex py-6">
                                                    <div
                                                        class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-zinc-700">
                                                        <img :src="item.image_url" :alt="item.name"
                                                            class="h-full w-full object-cover object-center">
                                                    </div>
                                                    <div class="ml-4 flex flex-1 flex-col">
                                                        <div>
                                                            <div
                                                                class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                                <h3><a :href="'#'"
                                                                        x-text="item.name"></a></h3>
                                                                <p class="ml-4"
                                                                    x-text="(window.__CURRENCY_SYMBOL__ || '$') + (item.price * item.quantity).toFixed(2)">
                                                                </p>
                                                            </div>
                                                            <p class="mt-1 text-sm text-gray-500"
                                                                x-text="item.variant_name"></p>
                                                        </div>
                                                        <div class="flex flex-1 items-end justify-between text-sm">
                                                            <div class="flex items-center gap-2">
                                                                <button
                                                                    @click="$store.cart.update(item.id, item.quantity - 1)"
                                                                    class="text-gray-500 hover:text-gray-700">-</button>
                                                                <p class="text-gray-500"
                                                                    x-text="'{{ __('Qty ') }}' + item.quantity">
                                                                </p>
                                                                <button
                                                                    @click="$store.cart.add(item.product_id, 1)"
                                                                    class="text-gray-500 hover:text-gray-700">+</button>
                                                            </div>
                                                            <div class="flex">
                                                                <button @click="$store.cart.remove(item.id)"
                                                                    type="button"
                                                                    class="font-medium text-red-500 hover:text-red-600 transition-colors p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20"
                                                                    title="{{ __('Remove') }}">
                                                                    <svg class="w-5 h-5" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                        </path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </template>
                                            <li x-show="$store.cart.items.length === 0"
                                                class="py-6 text-center text-gray-500">
                                                {{ __('Your cart is empty.') }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 dark:border-zinc-800 px-4 py-6 sm:px-6"
                                x-show="$store.cart.items.length > 0" x-cloak>
                                <div class="space-y-3">
                                    <div
                                        class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                        <p>{{ __('Subtotal') }}</p>
                                        <p x-text="(window.__CURRENCY_SYMBOL__ || '$') + parseFloat($store.cart.subtotal).toFixed(2)"></p>
                                    </div>
                                    <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                                        <p>{{ __('IVA') }} ({{ \App\Models\GeneralSetting::getTaxRatePercentage() }}%)</p>
                                        <p x-text="(window.__CURRENCY_SYMBOL__ || '$') + parseFloat($store.cart.tax_amount).toFixed(2)"></p>
                                    </div>
                                    <div
                                        class="flex justify-between text-lg font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-100 dark:border-zinc-800">
                                        <p>{{ __('Total') }}</p>
                                        <p x-text="(window.__CURRENCY_SYMBOL__ || '$') + parseFloat($store.cart.total).toFixed(2)"></p>
                                    </div>
                                </div>
                                <p class="mt-0.5 text-sm text-gray-500">
                                    {{ __('Shipping and taxes calculated at checkout.') }}</p>
                                <div class="mt-6">
                                    <a href="{{ route('checkout.index') }}"
                                        class="flex items-center justify-center rounded-xl border border-transparent bg-primary-600 px-6 py-4 text-base font-bold text-white shadow-lg shadow-primary-500/30 hover:bg-primary-700 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                                        {{ __('Checkout') }}
                                    </a>
                                </div>
                                <div class="mt-6 flex justify-center text-center text-sm text-gray-500">
                                    <p>
                                        {{ __('or') }}
                                        <button @click="$store.cart.toggle()" type="button"
                                            class="font-medium text-indigo-600 hover:text-indigo-500">
                                            {{ __('Continue Shopping') }}
                                            <span aria-hidden="true"> &rarr;</span>
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
