<div class="lg:sticky lg:top-24 space-y-6">
    <div
        class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6 overflow-hidden">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">
            {{ __('Resumen del Pedido') }}</h2>

        {{-- Items List --}}
        <div class="max-h-[250px] md:max-h-[400px] overflow-y-auto pr-2 -mr-2 space-y-4">
            <template x-for="item in $store.cart.items" :key="item.id">
                <div class="flex gap-4">
                    <div
                        class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
                        <img :src="item.image_url" :alt="item.name"
                            class="h-full w-full object-cover object-center">
                    </div>
                    <div class="flex flex-1 flex-col">
                        <div
                            class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                            <h3 class="line-clamp-1" x-text="item.name"></h3>
                            <p class="ml-4"
                                x-text="(window.__CURRENCY_SYMBOL__ || '$') + (item.price * item.quantity).toFixed(2)"></p>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Cant.') }} <span x-text="item.quantity"></span>
                        </p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Coupon Code --}}
        <div class="border-t border-gray-100 dark:border-zinc-800 mt-6 pt-4"
            x-data="{ couponInput: '', couponLoading: false, couponError: '' }">
            <template x-if="!$store.cart.coupon_code">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">{{ __('Cupón de descuento') }}</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="couponInput"
                            class="flex-1 rounded-lg border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="{{ __('Ingrese código') }}"
                            @keydown.enter.prevent="couponLoading = true; couponError = ''; $store.cart.applyCoupon(couponInput).then(r => { couponLoading = false; if (!r.success) couponError = r.message; else couponInput = ''; })">
                        <button type="button"
                            @click="couponLoading = true; couponError = ''; $store.cart.applyCoupon(couponInput).then(r => { couponLoading = false; if (!r.success) couponError = r.message; else couponInput = ''; })"
                            :disabled="couponLoading || !couponInput"
                            class="rounded-lg bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200 disabled:opacity-50 transition-colors">
                            <span x-show="!couponLoading">{{ __('Aplicar') }}</span>
                            <span x-show="couponLoading" x-cloak>...</span>
                        </button>
                    </div>
                    <p x-show="couponError" x-text="couponError" x-cloak class="text-xs text-red-500 mt-1"></p>
                </div>
            </template>
            <template x-if="$store.cart.coupon_code">
                <div class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 rounded-lg px-3 py-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <span class="text-sm font-medium text-green-700 dark:text-green-400" x-text="$store.cart.coupon_code"></span>
                    </div>
                    <button type="button" @click="$store.cart.removeCoupon()"
                        class="text-xs text-red-500 hover:text-red-700 font-medium">{{ __('Quitar') }}</button>
                </div>
            </template>
        </div>

        {{-- Totals --}}
        <div class="border-t border-gray-100 dark:border-zinc-800 mt-4 pt-4 space-y-3">
            <div class="flex justify-between text-sm">
                <p class="text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</p>
                <p class="font-medium text-gray-900 dark:text-white"
                    x-text="(window.__CURRENCY_SYMBOL__ || '$') + parseFloat($store.cart.subtotal || 0).toFixed(2)"></p>
            </div>
            <div x-show="$store.cart.discount_amount > 0" x-cloak
                class="flex justify-between text-sm text-green-600 dark:text-green-400">
                <p>{{ __('Descuento') }}</p>
                <p class="font-medium" x-text="'-' + (window.__CURRENCY_SYMBOL__ || '$') + parseFloat($store.cart.discount_amount || 0).toFixed(2)"></p>
            </div>
            <div class="flex justify-between text-sm">
                <p class="text-gray-500 dark:text-gray-400">{{ __('IVA') }} ({{ \App\Models\GeneralSetting::getTaxRatePercentage() }}%)</p>
                <p class="font-medium text-gray-900 dark:text-white"
                    x-text="(window.__CURRENCY_SYMBOL__ || '$') + parseFloat($store.cart.tax_amount || 0).toFixed(2)"></p>
            </div>
            <div class="flex justify-between text-sm">
                <p class="text-gray-500 dark:text-gray-400">{{ __('Envío') }}</p>
                <template x-if="shippingRates.length === 0">
                    <p class="font-medium text-green-600">{{ __('Gratis') }}</p>
                </template>
                <template x-if="shippingRates.length > 0 && shippingCost === 0">
                    <p class="font-medium text-green-600">{{ __('Gratis') }}</p>
                </template>
                <template x-if="shippingRates.length > 0 && shippingCost > 0">
                    <p class="font-medium text-gray-900 dark:text-white" x-text="(window.__CURRENCY_SYMBOL__ || '$') + shippingCost.toFixed(2)"></p>
                </template>
            </div>

            {{-- Payment Surcharge --}}
            <div x-show="surchargePercentage > 0" x-cloak
                class="flex justify-between text-sm text-amber-600 dark:text-amber-500">
                <p>
                    {{ __('Recargo Método Pago') }}
                    (<span x-text="surchargePercentage.toFixed(2) + '%'"></span>)
                </p>
                <p class="font-medium" x-text="(window.__CURRENCY_SYMBOL__ || '$') + surchargeAmount.toFixed(2)"></p>
            </div>

            <div
                class="border-t border-gray-100 dark:border-zinc-800 pt-4 flex justify-between items-center bg-gray-50 dark:bg-zinc-800/50 p-4 -mx-6 -mb-6 mt-4">
                <p class="text-base font-bold text-gray-900 dark:text-white">{{ __('Total') }}
                </p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white"
                    x-text="(window.__CURRENCY_SYMBOL__ || '$') + finalTotal.toFixed(2)"></p>
            </div>
        </div>
    </div>

    @auth
        {{-- Trust Badges --}}
        <div class="flex items-center justify-center gap-4 text-gray-400 opacity-60 grayscale">
            <div class="h-8 w-12 bg-gray-200 rounded"></div>
            <div class="h-8 w-12 bg-gray-200 rounded"></div>
            <div class="h-8 w-12 bg-gray-200 rounded"></div>
        </div>
    @endauth
</div>
