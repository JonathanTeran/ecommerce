<x-filament-panels::page>
    @php
        $revenue = $this->getRevenueKpis();
        $orders = $this->getOrderKpis();
        $customers = $this->getCustomerKpis();
        $inventory = $this->getInventoryKpis();
        $monthlyData = $this->getMonthlyRevenue();
        $statusDist = $this->getOrderStatusDistribution();
        $topProducts = $this->getTopProductsData();
        $recentOrders = $this->getRecentOrders();
    @endphp

    {{-- Row 1: Revenue KPIs --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-5 text-white shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <span class="text-emerald-100 text-xs font-semibold uppercase tracking-wider">{{ __('Revenue Today') }}</span>
                <x-heroicon-s-banknotes class="w-6 h-6 text-emerald-200/60" />
            </div>
            <div class="text-2xl font-bold">${{ number_format($revenue['today'], 2) }}</div>
        </div>

        <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 p-5 text-white shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <span class="text-blue-100 text-xs font-semibold uppercase tracking-wider">{{ __('This Month') }}</span>
                <x-heroicon-s-calendar class="w-6 h-6 text-blue-200/60" />
            </div>
            <div class="text-2xl font-bold">${{ number_format($revenue['this_month'], 2) }}</div>
            <div class="mt-1 flex items-center gap-1 text-xs">
                @if($revenue['growth_pct'] >= 0)
                    <x-heroicon-s-arrow-trending-up class="w-4 h-4" />
                    <span>+{{ $revenue['growth_pct'] }}%</span>
                @else
                    <x-heroicon-s-arrow-trending-down class="w-4 h-4" />
                    <span>{{ $revenue['growth_pct'] }}%</span>
                @endif
                <span class="text-blue-200">vs mes anterior</span>
            </div>
        </div>

        <div class="rounded-xl bg-gradient-to-br from-violet-500 to-violet-700 p-5 text-white shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <span class="text-violet-100 text-xs font-semibold uppercase tracking-wider">{{ __('Annual Revenue') }}</span>
                <x-heroicon-s-chart-bar class="w-6 h-6 text-violet-200/60" />
            </div>
            <div class="text-2xl font-bold">${{ number_format($revenue['this_year'], 2) }}</div>
        </div>

        <div class="rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 p-5 text-white shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <span class="text-amber-100 text-xs font-semibold uppercase tracking-wider">{{ __('Avg Order Value') }}</span>
                <x-heroicon-s-receipt-percent class="w-6 h-6 text-amber-200/60" />
            </div>
            <div class="text-2xl font-bold">${{ number_format($orders['avg_order_value'], 2) }}</div>
        </div>
    </div>

    {{-- Row 2: Orders + Customers + Inventory mini cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
        <x-filament::section class="lg:col-span-1">
            <div class="text-center">
                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $orders['this_month'] }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ __('Orders this month') }}</div>
                <div class="mt-1 text-xs {{ $orders['growth_pct'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $orders['growth_pct'] >= 0 ? '+' : '' }}{{ $orders['growth_pct'] }}%
                </div>
            </div>
        </x-filament::section>

        <x-filament::section class="lg:col-span-1">
            <div class="text-center">
                <div class="text-3xl font-bold text-amber-600">{{ $orders['pending'] }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ __('Pending') }}</div>
            </div>
        </x-filament::section>

        <x-filament::section class="lg:col-span-1">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $orders['processing'] }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ __('Processing') }}</div>
            </div>
        </x-filament::section>

        <x-filament::section class="lg:col-span-1">
            <div class="text-center">
                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($customers['total']) }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ __('Total Customers') }}</div>
                <div class="mt-1 text-xs text-emerald-600">+{{ $customers['new_this_month'] }} {{ __('new') }}</div>
            </div>
        </x-filament::section>

        <x-filament::section class="lg:col-span-1">
            <div class="text-center">
                <div class="text-3xl font-bold text-violet-600">{{ $customers['conversion_rate'] }}%</div>
                <div class="text-xs text-gray-500 mt-1">{{ __('Conversion') }}</div>
            </div>
        </x-filament::section>

        <x-filament::section class="lg:col-span-1">
            <div class="text-center">
                <div class="text-3xl font-bold text-cyan-600">{{ $customers['repeat_rate'] }}%</div>
                <div class="text-xs text-gray-500 mt-1">{{ __('Repeat Rate') }}</div>
            </div>
        </x-filament::section>
    </div>

    {{-- Row 3: Charts --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Revenue Chart (2/3 width) --}}
        <x-filament::section class="lg:col-span-2" heading="{{ __('Monthly Revenue & Orders (12 months)') }}" icon="heroicon-o-chart-bar">
            <div style="height: 320px;">
                <canvas id="ceoRevenueChart"></canvas>
            </div>
        </x-filament::section>

        {{-- Order Status Pie (1/3 width) --}}
        <x-filament::section heading="{{ __('Order Status') }}" icon="heroicon-o-chart-pie">
            <div style="height: 320px;">
                <canvas id="ceoStatusChart"></canvas>
            </div>
        </x-filament::section>
    </div>

    {{-- Row 4: Tables --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Top Products --}}
        <x-filament::section heading="{{ __('Top Products by Revenue') }}" icon="heroicon-o-trophy">
            @if($topProducts->isEmpty())
                <div class="py-8 text-center text-gray-400">
                    <x-heroicon-o-shopping-bag class="w-10 h-10 mx-auto mb-2 opacity-50" />
                    <p class="text-sm">{{ __('No sales data yet') }}</p>
                </div>
            @else
                <div class="space-y-3">
                    @php $maxRevenue = $topProducts->max('revenue') ?: 1; @endphp
                    @foreach($topProducts as $i => $p)
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                {{ $i === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
                                   ($i === 1 ? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' :
                                   'bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400') }}">
                                {{ $i + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $p->name }}</span>
                                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 ml-2">${{ number_format($p->revenue, 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5">
                                    <div class="bg-primary-500 h-1.5 rounded-full transition-all" style="width: {{ ($p->revenue / $maxRevenue) * 100 }}%"></div>
                                </div>
                                <div class="text-[11px] text-gray-400 mt-0.5">{{ number_format($p->qty) }} {{ __('units sold') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Recent Orders --}}
        <x-filament::section heading="{{ __('Latest Orders') }}" icon="heroicon-o-clipboard-document-list">
            @if($recentOrders->isEmpty())
                <div class="py-8 text-center text-gray-400">
                    <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-2 opacity-50" />
                    <p class="text-sm">{{ __('No orders yet') }}</p>
                </div>
            @else
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-400 uppercase">{{ __('Order') }}</th>
                                <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-400 uppercase">{{ __('Customer') }}</th>
                                <th class="px-3 py-2 text-center text-[11px] font-semibold text-gray-400 uppercase">{{ __('Status') }}</th>
                                <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-400 uppercase">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-3 py-2.5">
                                        <span class="font-mono text-xs font-medium text-gray-700 dark:text-gray-300">{{ $order->order_number }}</span>
                                    </td>
                                    <td class="px-3 py-2.5 text-gray-600 dark:text-gray-400 text-xs">{{ $order->user?->name ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-center">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                                'confirmed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                'processing' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
                                                'shipped' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
                                                'delivered' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $statusColors[$order->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $order->status->value }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right font-semibold text-gray-900 dark:text-white text-xs">${{ number_format($order->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>

    {{-- Row 5: Inventory Health --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-filament::section>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <x-heroicon-s-cube class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $inventory['total_products'] }}</div>
                    <div class="text-xs text-gray-500">{{ __('Active Products') }}</div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                    <x-heroicon-s-currency-dollar class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">${{ number_format($inventory['inventory_value'], 0) }}</div>
                    <div class="text-xs text-gray-500">{{ __('Inventory Value') }}</div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30">
                    <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-amber-600">{{ $inventory['low_stock'] }}</div>
                    <div class="text-xs text-gray-500">{{ __('Low Stock') }}</div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30">
                    <x-heroicon-s-x-circle class="w-5 h-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-red-600">{{ $inventory['out_of_stock'] }}</div>
                    <div class="text-xs text-gray-500">{{ __('Out of Stock') }}</div>
                </div>
            </div>
        </x-filament::section>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
        const textColor = isDark ? '#9ca3af' : '#6b7280';

        // Revenue + Orders Chart
        new Chart(document.getElementById('ceoRevenueChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyData['labels']) !!},
                datasets: [{
                    label: '{{ __("Revenue") }}',
                    data: {!! json_encode($monthlyData['revenues']) !!},
                    backgroundColor: isDark ? 'rgba(16, 185, 129, 0.6)' : 'rgba(16, 185, 129, 0.75)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 6,
                    yAxisID: 'y',
                    order: 2,
                }, {
                    label: '{{ __("Orders") }}',
                    data: {!! json_encode($monthlyData['orders']) !!},
                    type: 'line',
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    yAxisID: 'y1',
                    order: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', padding: 20 } },
                    tooltip: {
                        backgroundColor: isDark ? '#1f2937' : '#ffffff',
                        titleColor: isDark ? '#f3f4f6' : '#111827',
                        bodyColor: isDark ? '#d1d5db' : '#4b5563',
                        borderColor: isDark ? '#374151' : '#e5e7eb',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: ctx => {
                                if (ctx.dataset.yAxisID === 'y') return '{{ __("Revenue") }}: $' + ctx.parsed.y.toLocaleString(undefined, {minimumFractionDigits:2});
                                return '{{ __("Orders") }}: ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: { ticks: { color: textColor, maxRotation: 45, font: { size: 11 } }, grid: { display: false } },
                    y: { position: 'left', ticks: { color: textColor, callback: v => (window.__CURRENCY_SYMBOL__ || '$') + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v), font: { size: 11 } }, grid: { color: gridColor } },
                    y1: { position: 'right', ticks: { color: textColor, font: { size: 11 } }, grid: { display: false } }
                }
            }
        });

        // Status Doughnut
        @if(!empty($statusDist['values']))
        new Chart(document.getElementById('ceoStatusChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($statusDist['labels']) !!},
                datasets: [{
                    data: {!! json_encode($statusDist['values']) !!},
                    backgroundColor: {!! json_encode($statusDist['colors']) !!},
                    borderWidth: 3,
                    borderColor: isDark ? '#1f2937' : '#ffffff',
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', padding: 12, font: { size: 11 } } }
                }
            }
        });
        @endif
    });
    </script>
</x-filament-panels::page>
