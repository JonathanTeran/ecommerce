<div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-5 md:p-8">
    <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-4 md:mb-6 flex items-center gap-3">
        <span
            class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-600 text-sm font-bold">1</span>
        {{ __('Dirección de Envío') }}
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="col-span-2 md:col-span-1 space-y-2">
            <label
                class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Nombre Completo') }}</label>
            <input type="text" x-model="shipping.name"
                class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="col-span-2 md:col-span-1 space-y-2">
            <label
                class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Cédula / RUC') }}</label>
            <input type="text" x-model="shipping.identity_document" placeholder="1712345678"
                maxlength="13"
                class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="col-span-2 md:col-span-1 space-y-2">
            <label
                class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Correo Electrónico') }}</label>
            <input type="email" x-model="shipping.email"
                class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="col-span-2 space-y-2">
            <label
                class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Dirección') }}</label>
            <input type="text" x-model="shipping.address"
                class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="space-y-2">
            <label
                class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Ciudad') }}</label>
            <input type="text" x-model="shipping.city"
                class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="space-y-2">
            <label
                class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Provincia / Estado') }}</label>
            <input type="text" x-model="shipping.state"
                class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="space-y-2">
            <label
                class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Código Postal') }}</label>
            <input type="text" x-model="shipping.zip"
                class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="space-y-2">
            <label
                class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Teléfono') }}</label>
            <input type="text" x-model="shipping.phone"
                class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-white dark:placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
        </div>
    </div>
</div>
