<div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-8">
    <div class="text-center space-y-6">
        <div class="flex justify-center">
            <div class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/20 flex items-center justify-center">
                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('Solicitar Cotización') }}
            </h2>
            <p class="mt-2 text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                {{ __('Esta tienda trabaja bajo el modelo de cotizaciones. Solicita una cotización con los productos de tu carrito y recibirás una propuesta personalizada.') }}
            </p>
        </div>

        <a href="{{ route('quotation.create') }}"
            class="inline-flex items-center justify-center px-8 py-4 bg-primary-600 text-white rounded-xl font-bold text-lg shadow-lg shadow-primary-500/30 hover:bg-primary-700 hover:shadow-xl hover:-translate-y-0.5 transition-all gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            {{ __('Solicitar Cotización') }}
        </a>

        <div class="flex items-center justify-center gap-4 text-sm text-gray-400 dark:text-gray-500">
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ __('Respuesta en 24-48 horas') }}
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ __('Sin compromiso') }}
            </div>
        </div>
    </div>
</div>
