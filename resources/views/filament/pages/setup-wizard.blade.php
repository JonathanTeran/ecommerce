<x-filament-panels::page>
    <div class="mx-auto max-w-3xl">
        <div class="mb-8 text-center">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/10 mb-4">
                <x-heroicon-o-rocket-launch class="h-8 w-8 text-primary-600 dark:text-primary-400" />
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Configura tu tienda en minutos</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Completa estos pasos y tu tienda estara lista para vender</p>
        </div>

        <form wire:submit="create">
            {{ $this->form }}
        </form>
    </div>
</x-filament-panels::page>
