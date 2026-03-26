<x-filament-panels::page>
    @php
        $stats = $this->getStats();
    @endphp

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-danger-600">{{ number_format($stats['total_wishlisted']) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total en Wishlists</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-primary-600">{{ number_format($stats['unique_products']) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Productos Únicos</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-info-600">{{ number_format($stats['unique_users']) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Usuarios con Wishlist</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold {{ $stats['out_of_stock_wishlisted'] > 0 ? 'text-warning-600' : 'text-gray-400' }}">
                    {{ number_format($stats['out_of_stock_wishlisted']) }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Deseados sin Stock</div>
            </div>
        </x-filament::section>
    </div>

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
