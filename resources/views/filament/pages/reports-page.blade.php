<x-filament-panels::page>
    @php
        $sales = $this->getSalesReport();
        $topProducts = $this->getTopProductsReport();
        $topCustomers = $this->getTopCustomersReport();
        $categoryPerformance = $this->getCategoryPerformance();
        $paymentBreakdown = $this->getPaymentMethodBreakdown();
        $conversion = $this->getOrderConversionRate();
        $avgOrderValue = $this->getAverageOrderValue();
        $orderStatusBreakdown = $this->getOrderStatusBreakdown();
        $returnsReport = $this->getReturnsReport();
        $inventoryReport = $this->getInventoryReport();
        $supportReport = $this->getSupportReport();
        $revenueByMonth = $this->getRevenueByMonth();
        $customerSegments = $this->getNewVsReturningCustomers();
    @endphp

    {{-- Date Range Filter --}}
    <x-filament::section>
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label for="dateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('From') }}</label>
                <input type="date" id="dateFrom" wire:model.live.debounce.500ms="dateFrom"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            </div>
            <div>
                <label for="dateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('To') }}</label>
                <input type="date" id="dateTo" wire:model.live.debounce.500ms="dateTo"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            </div>
        </div>
    </x-filament::section>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-filament::section>
            <div class="text-center space-y-1">
                <x-heroicon-o-banknotes class="w-8 h-8 mx-auto text-emerald-500" />
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($sales['total_revenue'], 2) }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Total Revenue') }}</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center space-y-1">
                <x-heroicon-o-shopping-cart class="w-8 h-8 mx-auto text-blue-500" />
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($sales['total_orders']) }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Total Orders') }}</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center space-y-1">
                <x-heroicon-o-receipt-percent class="w-8 h-8 mx-auto text-amber-500" />
                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">${{ number_format($avgOrderValue, 2) }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Avg Order Value') }}</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center space-y-1">
                <x-heroicon-o-arrow-path class="w-8 h-8 mx-auto text-violet-500" />
                <div class="text-2xl font-bold text-violet-600 dark:text-violet-400">{{ $conversion['rate'] }}%</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    {{ __('Conversion Rate') }}
                    <span class="text-[10px]">({{ $conversion['converted'] }}/{{ $conversion['total'] }})</span>
                </div>
            </div>
        </x-filament::section>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Sales Chart --}}
        <x-filament::section heading="{{ __('Daily Revenue') }}" icon="heroicon-o-chart-bar">
            @if($sales['daily']->isEmpty())
                <div class="flex items-center justify-center h-48 text-gray-400">
                    <div class="text-center">
                        <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p class="text-sm">{{ __('No data for selected range') }}</p>
                    </div>
                </div>
            @else
                <div style="height: 280px;">
                    <canvas id="salesChart"></canvas>
                </div>
            @endif
        </x-filament::section>

        {{-- Category Performance Chart --}}
        <x-filament::section heading="{{ __('Revenue by Category') }}" icon="heroicon-o-tag">
            @if($categoryPerformance->isEmpty())
                <div class="flex items-center justify-center h-48 text-gray-400">
                    <div class="text-center">
                        <x-heroicon-o-chart-pie class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p class="text-sm">{{ __('No data for selected range') }}</p>
                    </div>
                </div>
            @else
                <div style="height: 280px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            @endif
        </x-filament::section>
    </div>

    {{-- Tables Row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Top Products --}}
        <x-filament::section heading="{{ __('Top 10 Products') }}" icon="heroicon-o-shopping-bag">
            @if($topProducts->isEmpty())
                <p class="text-sm text-gray-500">{{ __('No data for selected range') }}</p>
            @else
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Product') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Qty') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($topProducts as $index => $product)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-3 py-2.5 text-gray-400 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-3 py-2.5 font-medium text-gray-900 dark:text-white">{{ $product->name }}</td>
                                    <td class="px-3 py-2.5 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                            {{ number_format($product->total_quantity) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format($product->total_revenue, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Top Customers --}}
        <x-filament::section heading="{{ __('Top 10 Customers') }}" icon="heroicon-o-users">
            @if($topCustomers->isEmpty())
                <p class="text-sm text-gray-500">{{ __('No data for selected range') }}</p>
            @else
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Customer') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Orders') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Spent') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($topCustomers as $index => $customer)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-3 py-2.5 text-gray-400 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-3 py-2.5">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $customer->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $customer->email }}</div>
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            {{ number_format($customer->orders_count) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format($customer->total_spent, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Category Performance Table --}}
        <x-filament::section heading="{{ __('Category Performance') }}" icon="heroicon-o-squares-2x2">
            @if($categoryPerformance->isEmpty())
                <p class="text-sm text-gray-500">{{ __('No data for selected range') }}</p>
            @else
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Category') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Units') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Revenue') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @php $totalCatRevenue = $categoryPerformance->sum('total_revenue'); @endphp
                            @foreach($categoryPerformance as $category)
                                @php $pct = $totalCatRevenue > 0 ? ($category->total_revenue / $totalCatRevenue) * 100 : 0; @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-3 py-2.5 font-medium text-gray-900 dark:text-white">{{ $category->name }}</td>
                                    <td class="px-3 py-2.5 text-right">{{ number_format($category->total_units) }}</td>
                                    <td class="px-3 py-2.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format($category->total_revenue, 2) }}</td>
                                    <td class="px-3 py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                                <div class="bg-primary-500 h-1.5 rounded-full" style="width: {{ min($pct, 100) }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 w-10 text-right">{{ number_format($pct, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Payment Method Breakdown --}}
        <x-filament::section heading="{{ __('Payment Methods') }}" icon="heroicon-o-credit-card">
            @if($paymentBreakdown->isEmpty())
                <p class="text-sm text-gray-500">{{ __('No data for selected range') }}</p>
            @else
                <div class="space-y-3">
                    @php $totalPayRevenue = $paymentBreakdown->sum('total_revenue'); @endphp
                    @foreach($paymentBreakdown as $method)
                        @php $pct = $totalPayRevenue > 0 ? ($method->total_revenue / $totalPayRevenue) * 100 : 0; @endphp
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white text-sm">{{ $method->payment_method ?? __('Not specified') }}</div>
                                <div class="text-xs text-gray-400">{{ number_format($method->orders_count) }} {{ __('orders') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($method->total_revenue, 2) }}</div>
                                <div class="text-xs text-gray-400">{{ number_format($pct, 1) }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    </div>

    {{-- Additional Reports Row --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-filament::section>
            <div class="text-center space-y-1">
                <x-heroicon-o-arrow-uturn-left class="w-8 h-8 mx-auto text-red-500" />
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $returnsReport['total'] }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Devoluciones</div>
                <div class="text-[10px] text-gray-400">{{ $returnsReport['pending'] }} pendientes</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center space-y-1">
                <x-heroicon-o-cube class="w-8 h-8 mx-auto text-orange-500" />
                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $inventoryReport['low_stock'] }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Stock Bajo</div>
                <div class="text-[10px] text-gray-400">{{ $inventoryReport['out_of_stock'] }} agotados</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center space-y-1">
                <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 mx-auto text-indigo-500" />
                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $supportReport['open'] }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Tickets Abiertos</div>
                <div class="text-[10px] text-gray-400">{{ $supportReport['avg_resolution_hours'] }}h promedio</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center space-y-1">
                <x-heroicon-o-user-group class="w-8 h-8 mx-auto text-teal-500" />
                <div class="text-2xl font-bold text-teal-600 dark:text-teal-400">{{ $customerSegments['new'] }} / {{ $customerSegments['returning'] }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Nuevos / Recurrentes</div>
                <div class="text-[10px] text-gray-400">{{ $customerSegments['total'] }} clientes total</div>
            </div>
        </x-filament::section>
    </div>

    {{-- Order Status & Revenue Trends --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Order Status Breakdown --}}
        <x-filament::section heading="Estado de Pedidos" icon="heroicon-o-clipboard-document-list">
            @if($orderStatusBreakdown->isEmpty())
                <p class="text-sm text-gray-500">Sin datos para el rango seleccionado</p>
            @else
                <div class="space-y-3">
                    @php $totalStatusOrders = $orderStatusBreakdown->sum('count'); @endphp
                    @foreach($orderStatusBreakdown as $status)
                        @php $pct = $totalStatusOrders > 0 ? ($status->count / $totalStatusOrders) * 100 : 0; @endphp
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($status->color === 'success') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($status->color === 'danger') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @elseif($status->color === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                    @elseif($status->color === 'info') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                    @elseif($status->color === 'primary') bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300
                                    @endif
                                ">{{ $status->status }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-primary-500 h-1.5 rounded-full" style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 w-10 text-right">{{ $status->count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Monthly Revenue Trend --}}
        <x-filament::section heading="Tendencia de Ingresos (12 meses)" icon="heroicon-o-arrow-trending-up">
            @if($revenueByMonth->isEmpty())
                <div class="flex items-center justify-center h-48 text-gray-400">
                    <div class="text-center">
                        <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p class="text-sm">Sin datos disponibles</p>
                    </div>
                </div>
            @else
                <div style="height: 280px;">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            @endif
        </x-filament::section>
    </div>

    {{-- Inventory & Returns --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Low Stock Products --}}
        <x-filament::section heading="Productos con Stock Bajo" icon="heroicon-o-exclamation-triangle">
            @if($inventoryReport['top_low_stock']->isEmpty())
                <p class="text-sm text-gray-500">No hay productos con stock bajo</p>
            @else
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Stock</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Umbral</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($inventoryReport['top_low_stock'] as $product)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-3 py-2.5 font-medium text-gray-900 dark:text-white">{{ $product->name }}</td>
                                    <td class="px-3 py-2.5 text-gray-500">{{ $product->sku }}</td>
                                    <td class="px-3 py-2.5 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            {{ $product->quantity }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right text-gray-500">{{ $product->low_stock_threshold }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20">
                    <p class="text-sm text-amber-700 dark:text-amber-400">
                        Valor total de inventario: <strong>${{ number_format($inventoryReport['total_value'], 2) }}</strong>
                    </p>
                </div>
            @endif
        </x-filament::section>

        {{-- Returns by Reason --}}
        <x-filament::section heading="Devoluciones por Motivo" icon="heroicon-o-arrow-uturn-left">
            @if($returnsReport['by_reason']->isEmpty())
                <p class="text-sm text-gray-500">Sin devoluciones en el rango seleccionado</p>
            @else
                <div class="space-y-3">
                    @foreach($returnsReport['by_reason'] as $reason)
                        @php
                            $reasonEnum = \App\Enums\ReturnReason::tryFrom($reason->reason);
                            $pct = $returnsReport['total'] > 0 ? ($reason->count / $returnsReport['total']) * 100 : 0;
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                            <div class="font-medium text-gray-900 dark:text-white text-sm">
                                {{ $reasonEnum?->label() ?? $reason->reason }}
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                                <span class="text-sm font-semibold w-8 text-right">{{ $reason->count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($returnsReport['refunded_amount'] > 0)
                    <div class="mt-3 p-3 rounded-lg bg-red-50 dark:bg-red-900/20">
                        <p class="text-sm text-red-700 dark:text-red-400">
                            Total reembolsado: <strong>${{ number_format($returnsReport['refunded_amount'], 2) }}</strong>
                        </p>
                    </div>
                @endif
            @endif
        </x-filament::section>
    </div>

    {{-- Chart.js Scripts --}}
    @if($sales['daily']->isNotEmpty() || $categoryPerformance->isNotEmpty() || $revenueByMonth->isNotEmpty())
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';

            @if($sales['daily']->isNotEmpty())
            new Chart(document.getElementById('salesChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($sales['daily']->pluck('date')->toArray()) !!},
                    datasets: [{
                        label: '{{ __("Revenue") }}',
                        data: {!! json_encode($sales['daily']->pluck('revenue')->toArray()) !!},
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        borderRadius: 4,
                    }, {
                        label: '{{ __("Orders") }}',
                        data: {!! json_encode($sales['daily']->pluck('orders_count')->toArray()) !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y1',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: { legend: { labels: { color: textColor, usePointStyle: true, pointStyle: 'circle' } } },
                    scales: {
                        x: { ticks: { color: textColor, maxRotation: 45 }, grid: { color: gridColor } },
                        y: { ticks: { color: textColor, callback: v => (window.__CURRENCY_SYMBOL__ || '$') + v.toLocaleString() }, grid: { color: gridColor }, position: 'left' },
                        y1: { ticks: { color: textColor }, grid: { display: false }, position: 'right' }
                    }
                }
            });
            @endif

            @if($categoryPerformance->isNotEmpty())
            new Chart(document.getElementById('categoryChart'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($categoryPerformance->pluck('name')->toArray()) !!},
                    datasets: [{
                        data: {!! json_encode($categoryPerformance->pluck('total_revenue')->toArray()) !!},
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],
                        borderWidth: 2,
                        borderColor: isDark ? '#1f2937' : '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', padding: 16 } },
                        tooltip: { callbacks: { label: ctx => ctx.label + ': $' + ctx.parsed.toLocaleString(undefined, {minimumFractionDigits: 2}) } }
                    }
                }
            });
            @endif

            @if($revenueByMonth->isNotEmpty())
            new Chart(document.getElementById('monthlyRevenueChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($revenueByMonth->pluck('month')->toArray()) !!},
                    datasets: [{
                        label: 'Ingresos',
                        data: {!! json_encode($revenueByMonth->pluck('revenue')->toArray()) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981',
                    }, {
                        label: 'Pedidos',
                        data: {!! json_encode($revenueByMonth->pluck('orders_count')->toArray()) !!},
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#3b82f6',
                        yAxisID: 'y1',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: { legend: { labels: { color: textColor, usePointStyle: true, pointStyle: 'circle' } } },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { color: gridColor } },
                        y: { ticks: { color: textColor, callback: v => (window.__CURRENCY_SYMBOL__ || '$') + v.toLocaleString() }, grid: { color: gridColor }, position: 'left' },
                        y1: { ticks: { color: textColor }, grid: { display: false }, position: 'right' }
                    }
                }
            });
            @endif
        });
    </script>
    @endif
</x-filament-panels::page>
