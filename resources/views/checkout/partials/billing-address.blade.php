<div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-5 md:p-8">
    <div class="flex items-center gap-3 mb-6">
        <input type="checkbox" id="same_as_shipping" x-model="sameAsShipping"
            class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
        <label for="same_as_shipping"
            class="text-base font-medium text-gray-900 dark:text-white select-none">
            {{ __('La dirección de facturación es la misma que la de envío') }}
        </label>
    </div>

    <div x-show="!sameAsShipping" x-collapse>
        <h2
            class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3 border-t border-gray-100 dark:border-zinc-800 pt-6">
            <span
                class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 text-sm font-bold">2</span>
            {{ __('Dirección de Facturación') }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2 md:col-span-1 space-y-2">
                <label
                    class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Nombre / Razón Social') }}</label>
                <input type="text" x-model="billing.name"
                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="col-span-2 md:col-span-1 space-y-2">
                <label
                    class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('RUC / CI / ID') }}</label>
                <input type="text" x-model="billing.tax_id"
                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="col-span-2 space-y-2">
                <label
                    class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Dirección de Facturación') }}</label>
                <input type="text" x-model="billing.address"
                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="space-y-2">
                <label
                    class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Ciudad') }}</label>
                <input type="text" x-model="billing.city"
                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="space-y-2">
                <label
                    class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Provincia / Estado') }}</label>
                <input type="text" x-model="billing.state"
                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="space-y-2">
                <label
                    class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Código Postal') }}</label>
                <input type="text" x-model="billing.zip"
                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="space-y-2">
                <label
                    class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Teléfono') }}</label>
                <input type="text" x-model="billing.phone"
                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>
    </div>
</div>
