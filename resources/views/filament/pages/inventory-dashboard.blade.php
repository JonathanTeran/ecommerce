<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $lowStockProducts = $this->getLowStockProducts();
        $outOfStockProducts = $this->getOutOfStockProducts();
        $recentMovements = $this->getRecentMovements();
        $pendingAlerts = $this->getPendingAlerts();
    @endphp

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-success-600">{{ number_format($stats['total_in_stock']) }}</div>
                <div class="text-sm text-gray-500">En Stock</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-warning-600">{{ number_format($stats['low_stock']) }}</div>
                <div class="text-sm text-gray-500">Stock Bajo</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-danger-600">{{ number_format($stats['out_of_stock']) }}</div>
                <div class="text-sm text-gray-500">Agotados</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold text-primary-600">${{ number_format($stats['inventory_value'], 2) }}</div>
                <div class="text-sm text-gray-500">Valor Inventario</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-2xl font-bold {{ $stats['pending_alerts'] > 0 ? 'text-danger-600' : 'text-gray-400' }}">
                    {{ number_format($stats['pending_alerts']) }}
                </div>
                <div class="text-sm text-gray-500">Alertas Pendientes</div>
            </div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Low Stock Products --}}
        <x-filament::section heading="Productos con Stock Bajo" icon="heroicon-o-exclamation-triangle">
            @if($lowStockProducts->isEmpty())
                <p class="text-sm text-gray-500">No hay productos con stock bajo.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-2 py-2 text-left">Producto</th>
                                <th class="px-2 py-2 text-right">Stock</th>
                                <th class="px-2 py-2 text-right">Umbral</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $product)
                                <tr class="border-b">
                                    <td class="px-2 py-2">
                                        <div class="font-medium">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $product->sku }}</div>
                                    </td>
                                    <td class="px-2 py-2 text-right font-bold text-warning-600">{{ $product->quantity }}</td>
                                    <td class="px-2 py-2 text-right text-gray-500">{{ $product->low_stock_threshold }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Out of Stock Products --}}
        <x-filament::section heading="Productos Agotados" icon="heroicon-o-x-circle">
            @if($outOfStockProducts->isEmpty())
                <p class="text-sm text-gray-500">No hay productos agotados.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-2 py-2 text-left">Producto</th>
                                <th class="px-2 py-2 text-right">Ventas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outOfStockProducts as $product)
                                <tr class="border-b">
                                    <td class="px-2 py-2">
                                        <div class="font-medium">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $product->sku }}</div>
                                    </td>
                                    <td class="px-2 py-2 text-right text-gray-500">{{ $product->sales_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Recent Movements --}}
        <x-filament::section heading="Últimos Movimientos" icon="heroicon-o-rectangle-stack">
            @if($recentMovements->isEmpty())
                <p class="text-sm text-gray-500">No hay movimientos registrados.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-2 py-2 text-left">Fecha</th>
                                <th class="px-2 py-2 text-left">Producto</th>
                                <th class="px-2 py-2 text-left">Tipo</th>
                                <th class="px-2 py-2 text-right">Cant.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMovements as $movement)
                                <tr class="border-b">
                                    <td class="px-2 py-2 text-xs text-gray-500">{{ $movement->created_at->format('d/m H:i') }}</td>
                                    <td class="px-2 py-2">{{ $movement->product?->name ?? '-' }}</td>
                                    <td class="px-2 py-2">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                            {{ $movement->quantity > 0 ? 'bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-400' : 'bg-danger-50 text-danger-700 dark:bg-danger-400/10 dark:text-danger-400' }}">
                                            {{ $movement->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 text-right font-mono {{ $movement->quantity > 0 ? 'text-success-600' : 'text-danger-600' }}">
                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Pending Alerts --}}
        <x-filament::section heading="Alertas Pendientes" icon="heroicon-o-bell-alert">
            @if($pendingAlerts->isEmpty())
                <p class="text-sm text-gray-500">No hay alertas pendientes.</p>
            @else
                <div class="space-y-3">
                    @foreach($pendingAlerts as $alert)
                        <div class="flex items-center justify-between rounded-lg border p-3
                            {{ $alert->type === 'out_of_stock' ? 'border-danger-200 bg-danger-50 dark:border-danger-400/20 dark:bg-danger-400/5' : 'border-warning-200 bg-warning-50 dark:border-warning-400/20 dark:bg-warning-400/5' }}">
                            <div>
                                <div class="font-medium text-sm">{{ $alert->product?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $alert->type_label }} - Cantidad: {{ $alert->current_quantity }}</div>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                {{ $alert->type === 'out_of_stock' ? 'bg-danger-100 text-danger-700' : 'bg-warning-100 text-warning-700' }}">
                                {{ $alert->type_label }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
