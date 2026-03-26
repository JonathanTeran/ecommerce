<x-filament-panels::page>
    @php
        $stats = $this->getStats();
    @endphp

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-danger-600">{{ number_format($stats['total_abandoned']) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Carritos Abandonados</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-warning-600">${{ number_format($stats['total_value'], 2) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Valor Potencial</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-info-600">{{ number_format($stats['reminded']) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Recordatorios Enviados</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-success-600">{{ number_format($stats['active_carts']) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Carritos Activos</div>
            </div>
        </x-filament::section>
    </div>

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
