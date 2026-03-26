<template x-if="shippingRates.length > 0">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-600 text-sm font-bold">3</span>
            {{ __('Método de Envío') }}
        </h2>
        <div class="space-y-3">
            <template x-for="rate in shippingRates" :key="rate.key">
                <div class="border rounded-xl cursor-pointer transition-all p-4"
                    :class="shippingRateId == rate.key ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-1 ring-primary-500' : 'border-gray-200 dark:border-zinc-700 hover:border-gray-300'"
                    @click="selectShippingRate(rate.key)">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                                :class="shippingRateId == rate.key ? 'border-primary-500' : 'border-gray-300 dark:border-zinc-600'">
                                <div class="w-2.5 h-2.5 rounded-full bg-primary-500" x-show="shippingRateId == rate.key"></div>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white" x-text="rate.carrier_name + ' - ' + rate.rate_name"></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-show="rate.estimated_days" x-text="'Entrega estimada: ' + rate.estimated_days + ' días'"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <template x-if="rate.price === 0">
                                <span class="font-bold text-green-600">{{ __('Gratis') }}</span>
                            </template>
                            <template x-if="rate.price > 0">
                                <span class="font-bold text-gray-900 dark:text-white" x-text="(window.__CURRENCY_SYMBOL__ || '$') + rate.price.toFixed(2)"></span>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
