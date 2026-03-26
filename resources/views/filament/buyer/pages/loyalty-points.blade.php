<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mb-6">
        <x-filament::section>
            <div class="text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $this->getProgramName() }}</p>
                <p class="text-3xl font-bold text-primary-600">{{ number_format($this->getPointsBalance()) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">puntos disponibles</p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">Valor en Efectivo</p>
                <p class="text-3xl font-bold text-green-600">{{ $this->getRedemptionValue() }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">disponible para canjear</p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">Cómo Ganar Puntos</p>
                <p class="text-sm mt-2">Gane puntos con cada compra. Canjee sus puntos como descuento en su próxima orden.</p>
            </div>
        </x-filament::section>
    </div>

    <x-filament::section>
        <x-slot name="heading">Historial de Transacciones</x-slot>
        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
