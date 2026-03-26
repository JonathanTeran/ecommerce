<div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-8 text-center">
    <div
        class="w-16 h-16 bg-primary-50 dark:bg-primary-900/20 text-primary-500 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
    </div>
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __('Checkout') }}</h2>
    <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">
        {{ __('Por favor inicia sesión o regístrate para completar tu compra.') }}
    </p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto">
        <button type="button" @click="$dispatch('open-auth-modal', { mode: 'login' })"
            class="flex-1 px-8 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition shadow-lg shadow-primary-500/20">
            {{ __('Iniciar Sesión') }}
        </button>
        <button type="button" @click="$dispatch('open-auth-modal', { mode: 'register' })"
            class="flex-1 px-8 py-3 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white border border-gray-200 dark:border-zinc-700 rounded-xl font-bold hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
            {{ __('Registrarse') }}
        </button>
    </div>
</div>
