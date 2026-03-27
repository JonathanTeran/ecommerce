<x-filament-panels::page>
    @php
        $revenue = $this->getRevenueKpis();
        $orders = $this->getOrderKpis();
        $customers = $this->getCustomerKpis();
        $inventory = $this->getInventoryKpis();
        $monthlyData = $this->getMonthlyRevenue();
        $daily7 = $this->getDailyRevenue7Days();
        $statusDist = $this->getOrderStatusDistribution();
        $catRevenue = $this->getCategoryRevenue();
        $paymentMethods = $this->getPaymentMethodBreakdown();
        $topProducts = $this->getTopProductsData();
        $recentOrders = $this->getRecentOrders();
        $weekly = $this->getWeeklyComparison();
    @endphp

    {{-- Row 1: Revenue Hero Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Today --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-6 text-white shadow-lg shadow-emerald-200/30 dark:shadow-emerald-900/30">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute -right-1 -top-1 h-14 w-14 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-100">Ingresos Hoy</span>
                    <x-heroicon-s-banknotes class="h-5 w-5 text-emerald-200/60" />
                </div>
                <div class="mt-3 text-3xl font-black tracking-tight">${{ number_format($revenue['today'], 2) }}</div>
                <div class="mt-2 flex items-center gap-2 text-xs text-emerald-100">
                    <span>Ayer: ${{ number_format($revenue['yesterday'], 2) }}</span>
                </div>
            </div>
        </div>

        {{-- This Month --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 p-6 text-white shadow-lg shadow-blue-200/30 dark:shadow-blue-900/30">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute -right-1 -top-1 h-14 w-14 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-100">Este Mes</span>
                    <x-heroicon-s-calendar class="h-5 w-5 text-blue-200/60" />
                </div>
                <div class="mt-3 text-3xl font-black tracking-tight">${{ number_format($revenue['this_month'], 2) }}</div>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    @if($revenue['growth_pct'] >= 0)
                        <x-heroicon-s-arrow-trending-up class="h-4 w-4 text-emerald-300" />
                        <span class="font-bold text-emerald-300">+{{ $revenue['growth_pct'] }}%</span>
                    @else
                        <x-heroicon-s-arrow-trending-down class="h-4 w-4 text-red-300" />
                        <span class="font-bold text-red-300">{{ $revenue['growth_pct'] }}%</span>
                    @endif
                    <span class="text-blue-200">vs mes anterior</span>
                </div>
            </div>
        </div>

        {{-- Annual --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-700 p-6 text-white shadow-lg shadow-violet-200/30 dark:shadow-violet-900/30">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute -right-1 -top-1 h-14 w-14 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-violet-100">Ingresos {{ now()->year }}</span>
                    <x-heroicon-s-chart-bar class="h-5 w-5 text-violet-200/60" />
                </div>
                <div class="mt-3 text-3xl font-black tracking-tight">${{ number_format($revenue['this_year'], 2) }}</div>
                <div class="mt-2 text-xs text-violet-200">Acumulado anual</div>
            </div>
        </div>

        {{-- Avg Order --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-6 text-white shadow-lg shadow-amber-200/30 dark:shadow-amber-900/30">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute -right-1 -top-1 h-14 w-14 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-100">Ticket Promedio</span>
                    <x-heroicon-s-receipt-percent class="h-5 w-5 text-amber-200/60" />
                </div>
                <div class="mt-3 text-3xl font-black tracking-tight">${{ number_format($orders['avg_order_value'], 2) }}</div>
                <div class="mt-2 text-xs text-amber-100">{{ $orders['this_month'] }} pedidos este mes</div>
            </div>
        </div>
    </div>

    {{-- Row 2: Quick Metrics Bar --}}
    <div class="grid grid-cols-3 gap-3 sm:grid-cols-6">
        @php
            $metrics = [
                ['value' => $orders['this_month'], 'label' => 'Pedidos Mes', 'color' => 'text-blue-600 dark:text-blue-400', 'pct' => $orders['growth_pct']],
                ['value' => $orders['pending'], 'label' => 'Pendientes', 'color' => 'text-amber-600 dark:text-amber-400'],
                ['value' => $orders['processing'], 'label' => 'Procesando', 'color' => 'text-violet-600 dark:text-violet-400'],
                ['value' => number_format($customers['total']), 'label' => 'Clientes', 'color' => 'text-gray-900 dark:text-white', 'sub' => '+' . $customers['new_this_month'] . ' nuevos'],
                ['value' => $customers['conversion_rate'] . '%', 'label' => 'Conversión', 'color' => 'text-emerald-600 dark:text-emerald-400'],
                ['value' => $customers['repeat_rate'] . '%', 'label' => 'Recompra', 'color' => 'text-cyan-600 dark:text-cyan-400'],
            ];
        @endphp
        @foreach($metrics as $m)
            <div class="rounded-xl border border-gray-100 bg-white p-3 text-center dark:border-gray-800 dark:bg-gray-900">
                <div class="text-2xl font-bold {{ $m['color'] }}">{{ $m['value'] }}</div>
                <div class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-gray-500">{{ $m['label'] }}</div>
                @if(isset($m['pct']))
                    <div class="mt-0.5 text-[10px] font-semibold {{ $m['pct'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $m['pct'] >= 0 ? '+' : '' }}{{ $m['pct'] }}%</div>
                @elseif(isset($m['sub']))
                    <div class="mt-0.5 text-[10px] text-emerald-600">{{ $m['sub'] }}</div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Row 3: Charts - Revenue + Daily + Status --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Monthly Revenue (large) --}}
        <x-filament::section class="lg:col-span-5" heading="Ingresos Mensuales" icon="heroicon-o-chart-bar">
            <div style="height: 300px;"><canvas id="ceoRevenueChart"></canvas></div>
        </x-filament::section>

        {{-- Daily 7-day trend --}}
        <x-filament::section class="lg:col-span-4" heading="Últimos 7 Días" icon="heroicon-o-arrow-trending-up">
            <div style="height: 300px;"><canvas id="ceoDailyChart"></canvas></div>
        </x-filament::section>

        {{-- Status Doughnut --}}
        <x-filament::section class="lg:col-span-3" heading="Estado Pedidos" icon="heroicon-o-chart-pie">
            <div style="height: 300px;"><canvas id="ceoStatusChart"></canvas></div>
        </x-filament::section>
    </div>

    {{-- Row 4: Category + Payment + Weekly Comparison --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Category Revenue --}}
        <x-filament::section heading="Ingresos por Categoría" icon="heroicon-o-tag">
            @if(empty($catRevenue['values']))
                <div class="py-8 text-center text-gray-400"><p class="text-sm">Sin datos</p></div>
            @else
                <div style="height: 260px;"><canvas id="ceoCategoryChart"></canvas></div>
            @endif
        </x-filament::section>

        {{-- Payment Methods --}}
        <x-filament::section heading="Métodos de Pago" icon="heroicon-o-credit-card">
            @if(empty($paymentMethods['values']))
                <div class="py-8 text-center text-gray-400"><p class="text-sm">Sin datos</p></div>
            @else
                <div style="height: 260px;"><canvas id="ceoPaymentChart"></canvas></div>
            @endif
        </x-filament::section>

        {{-- Weekly Comparison --}}
        <x-filament::section heading="Comparativa Semanal" icon="heroicon-o-scale">
            <div class="space-y-6 py-2">
                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-600 dark:text-gray-400">Ingresos</span>
                        <span class="text-xs font-semibold {{ $weekly['revenue_pct'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $weekly['revenue_pct'] >= 0 ? '+' : '' }}{{ $weekly['revenue_pct'] }}%</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-3">
                            <span class="w-20 text-xs text-gray-500">Esta sem.</span>
                            <div class="flex-1 rounded-full bg-gray-100 dark:bg-gray-800 h-4">
                                @php $maxWR = max($weekly['this_week_revenue'], $weekly['last_week_revenue'], 1); @endphp
                                <div class="h-4 rounded-full bg-emerald-500 flex items-center justify-end pr-2" style="width: {{ min(($weekly['this_week_revenue'] / $maxWR) * 100, 100) }}%">
                                    <span class="text-[9px] font-bold text-white">${{ number_format($weekly['this_week_revenue'], 0) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-20 text-xs text-gray-500">Sem. ant.</span>
                            <div class="flex-1 rounded-full bg-gray-100 dark:bg-gray-800 h-4">
                                <div class="h-4 rounded-full bg-gray-400 dark:bg-gray-600 flex items-center justify-end pr-2" style="width: {{ min(($weekly['last_week_revenue'] / $maxWR) * 100, 100) }}%">
                                    <span class="text-[9px] font-bold text-white">${{ number_format($weekly['last_week_revenue'], 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-600 dark:text-gray-400">Pedidos</span>
                        <span class="text-xs font-semibold {{ $weekly['orders_pct'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $weekly['orders_pct'] >= 0 ? '+' : '' }}{{ $weekly['orders_pct'] }}%</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-3">
                            <span class="w-20 text-xs text-gray-500">Esta sem.</span>
                            <div class="flex-1 rounded-full bg-gray-100 dark:bg-gray-800 h-4">
                                @php $maxWO = max($weekly['this_week_orders'], $weekly['last_week_orders'], 1); @endphp
                                <div class="h-4 rounded-full bg-blue-500 flex items-center justify-end pr-2" style="width: {{ min(($weekly['this_week_orders'] / $maxWO) * 100, 100) }}%">
                                    <span class="text-[9px] font-bold text-white">{{ $weekly['this_week_orders'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-20 text-xs text-gray-500">Sem. ant.</span>
                            <div class="flex-1 rounded-full bg-gray-100 dark:bg-gray-800 h-4">
                                <div class="h-4 rounded-full bg-gray-400 dark:bg-gray-600 flex items-center justify-end pr-2" style="width: {{ min(($weekly['last_week_orders'] / $maxWO) * 100, 100) }}%">
                                    <span class="text-[9px] font-bold text-white">{{ $weekly['last_week_orders'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    {{-- Row 5: Top Products + Recent Orders --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Top Products --}}
        <x-filament::section heading="Top Productos por Ingresos" icon="heroicon-o-trophy">
            @if($topProducts->isEmpty())
                <div class="py-8 text-center text-gray-400">
                    <x-heroicon-o-shopping-bag class="mx-auto mb-2 h-10 w-10 opacity-50" />
                    <p class="text-sm">Sin datos de ventas aún</p>
                </div>
            @else
                <div class="space-y-3">
                    @php $maxRevenue = $topProducts->max('revenue') ?: 1; @endphp
                    @foreach($topProducts as $i => $p)
                        @php
                            $medalColors = [
                                0 => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 ring-2 ring-amber-300/50',
                                1 => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 ring-2 ring-gray-300/50',
                                2 => 'bg-orange-100 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400 ring-2 ring-orange-300/50',
                            ];
                            $barColors = ['bg-amber-500', 'bg-gray-400', 'bg-orange-400', 'bg-blue-400', 'bg-violet-400'];
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $medalColors[$i] ?? 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-500' }}">
                                {{ $i + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $p->name }}</span>
                                    <span class="ml-2 text-sm font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($p->revenue, 2) }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                                    <div class="{{ $barColors[$i] ?? 'bg-primary-500' }} h-2 rounded-full transition-all" style="width: {{ ($p->revenue / $maxRevenue) * 100 }}%"></div>
                                </div>
                                <div class="mt-0.5 text-[10px] text-gray-400">{{ number_format($p->qty) }} unidades vendidas</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Recent Orders --}}
        <x-filament::section heading="Últimos Pedidos" icon="heroicon-o-clipboard-document-list">
            @if($recentOrders->isEmpty())
                <div class="py-8 text-center text-gray-400">
                    <x-heroicon-o-inbox class="mx-auto mb-2 h-10 w-10 opacity-50" />
                    <p class="text-sm">Sin pedidos aún</p>
                </div>
            @else
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-3 py-2 text-left text-[11px] font-semibold uppercase text-gray-400">Pedido</th>
                                <th class="px-3 py-2 text-left text-[11px] font-semibold uppercase text-gray-400">Cliente</th>
                                <th class="px-3 py-2 text-center text-[11px] font-semibold uppercase text-gray-400">Estado</th>
                                <th class="px-3 py-2 text-right text-[11px] font-semibold uppercase text-gray-400">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="px-3 py-2.5">
                                        <span class="font-mono text-xs font-medium text-gray-700 dark:text-gray-300">{{ $order->order_number }}</span>
                                        <div class="text-[10px] text-gray-400">{{ $order->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-gray-600 dark:text-gray-400">{{ $order->user?->name ?? '-' }}</td>
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
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusColors[$order->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $order->status->getLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right text-xs font-semibold text-gray-900 dark:text-white">${{ number_format($order->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>

    {{-- Row 6: Inventory Health --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $invCards = [
                ['icon' => 'heroicon-s-cube', 'value' => $inventory['total_products'], 'label' => 'Productos Activos', 'iconBg' => 'bg-blue-100 dark:bg-blue-900/30', 'iconColor' => 'text-blue-600 dark:text-blue-400', 'textColor' => 'text-gray-900 dark:text-white'],
                ['icon' => 'heroicon-s-currency-dollar', 'value' => '$' . number_format($inventory['inventory_value'], 0), 'label' => 'Valor Inventario', 'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'iconColor' => 'text-emerald-600 dark:text-emerald-400', 'textColor' => 'text-gray-900 dark:text-white'],
                ['icon' => 'heroicon-s-exclamation-triangle', 'value' => $inventory['low_stock'], 'label' => 'Stock Bajo', 'iconBg' => 'bg-amber-100 dark:bg-amber-900/30', 'iconColor' => 'text-amber-600 dark:text-amber-400', 'textColor' => 'text-amber-600'],
                ['icon' => 'heroicon-s-x-circle', 'value' => $inventory['out_of_stock'], 'label' => 'Agotados', 'iconBg' => 'bg-red-100 dark:bg-red-900/30', 'iconColor' => 'text-red-600 dark:text-red-400', 'textColor' => 'text-red-600'],
            ];
        @endphp
        @foreach($invCards as $card)
            <x-filament::section>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $card['iconBg'] }}">
                        <x-dynamic-component :component="$card['icon']" class="h-5 w-5 {{ $card['iconColor'] }}" />
                    </div>
                    <div>
                        <div class="text-xl font-bold {{ $card['textColor'] }}">{{ $card['value'] }}</div>
                        <div class="text-xs text-gray-500">{{ $card['label'] }}</div>
                    </div>
                </div>
            </x-filament::section>
        @endforeach
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const tooltipBg = isDark ? '#1f2937' : '#ffffff';
        const tooltipTitle = isDark ? '#f3f4f6' : '#111827';
        const tooltipBody = isDark ? '#d1d5db' : '#4b5563';
        const tooltipBorder = isDark ? '#374151' : '#e5e7eb';

        const tooltipStyle = {
            backgroundColor: tooltipBg, titleColor: tooltipTitle, bodyColor: tooltipBody,
            borderColor: tooltipBorder, borderWidth: 1, padding: 12, cornerRadius: 8,
            titleFont: { weight: 'bold' }, bodyFont: { size: 12 },
        };

        // Monthly Revenue Chart
        new Chart(document.getElementById('ceoRevenueChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyData['labels']) !!},
                datasets: [{
                    label: 'Ingresos',
                    data: {!! json_encode($monthlyData['revenues']) !!},
                    backgroundColor: isDark ? 'rgba(16,185,129,0.5)' : 'rgba(16,185,129,0.7)',
                    borderColor: '#10b981', borderWidth: 0, borderRadius: 6,
                    yAxisID: 'y', order: 2,
                }, {
                    label: 'Pedidos',
                    data: {!! json_encode($monthlyData['orders']) !!},
                    type: 'line', borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    fill: true, tension: 0.4, pointRadius: 3,
                    pointBackgroundColor: '#3b82f6', pointBorderWidth: 0,
                    yAxisID: 'y1', order: 1,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 11 } } },
                    tooltip: { ...tooltipStyle, callbacks: {
                        label: ctx => ctx.dataset.yAxisID === 'y' ? 'Ingresos: $' + ctx.parsed.y.toLocaleString(undefined,{minimumFractionDigits:2}) : 'Pedidos: ' + ctx.parsed.y
                    }}
                },
                scales: {
                    x: { ticks: { color: textColor, maxRotation: 45, font: { size: 10 } }, grid: { display: false } },
                    y: { position: 'left', ticks: { color: textColor, callback: v => '$' + (v>=1000?(v/1000).toFixed(0)+'k':v), font: { size: 10 } }, grid: { color: gridColor } },
                    y1: { position: 'right', ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } }
                }
            }
        });

        // Daily 7-day Chart
        new Chart(document.getElementById('ceoDailyChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($daily7['labels']) !!},
                datasets: [{
                    label: 'Ingresos',
                    data: {!! json_encode($daily7['revenues']) !!},
                    borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)',
                    fill: true, tension: 0.4, pointRadius: 5,
                    pointBackgroundColor: '#8b5cf6', pointBorderColor: isDark ? '#1f2937' : '#fff',
                    pointBorderWidth: 2, borderWidth: 3,
                }, {
                    label: 'Pedidos',
                    data: {!! json_encode($daily7['orders']) !!},
                    borderColor: '#f59e0b', backgroundColor: 'transparent',
                    fill: false, tension: 0.4, pointRadius: 4,
                    pointBackgroundColor: '#f59e0b', borderWidth: 2, borderDash: [5,5],
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 11 } } },
                    tooltip: { ...tooltipStyle, callbacks: {
                        label: ctx => ctx.datasetIndex === 0 ? 'Ingresos: $' + ctx.parsed.y.toLocaleString(undefined,{minimumFractionDigits:2}) : 'Pedidos: ' + ctx.parsed.y
                    }}
                },
                scales: {
                    x: { ticks: { color: textColor, font: { size: 11, weight: 'bold' } }, grid: { display: false } },
                    y: { ticks: { color: textColor, callback: v => '$' + v.toLocaleString(), font: { size: 10 } }, grid: { color: gridColor } },
                    y1: { position: 'right', ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } }
                }
            }
        });

        // Status Doughnut
        @if(!empty($statusDist['values']))
        new Chart(document.getElementById('ceoStatusChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($statusDist['labels']) !!},
                datasets: [{ data: {!! json_encode($statusDist['values']) !!}, backgroundColor: {!! json_encode($statusDist['colors']) !!}, borderWidth: 3, borderColor: isDark ? '#1f2937' : '#ffffff', hoverOffset: 8 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', padding: 10, font: { size: 10 } } },
                    tooltip: tooltipStyle,
                }
            }
        });
        @endif

        // Category Revenue
        @if(!empty($catRevenue['values']))
        new Chart(document.getElementById('ceoCategoryChart'), {
            type: 'polarArea',
            data: {
                labels: {!! json_encode($catRevenue['labels']) !!},
                datasets: [{ data: {!! json_encode($catRevenue['values']) !!}, backgroundColor: {!! json_encode(array_map(fn($c) => $c . 'cc', $catRevenue['colors'])) !!}, borderWidth: 0 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', padding: 10, font: { size: 10 } } },
                    tooltip: { ...tooltipStyle, callbacks: { label: ctx => ctx.label + ': $' + ctx.parsed.r.toLocaleString(undefined,{minimumFractionDigits:2}) } }
                },
                scales: { r: { ticks: { display: false }, grid: { color: gridColor } } }
            }
        });
        @endif

        // Payment Methods
        @if(!empty($paymentMethods['values']))
        new Chart(document.getElementById('ceoPaymentChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($paymentMethods['labels']) !!},
                datasets: [{
                    data: {!! json_encode($paymentMethods['values']) !!},
                    backgroundColor: {!! json_encode(array_slice(['rgba(16,185,129,0.7)','rgba(59,130,246,0.7)','rgba(245,158,11,0.7)','rgba(239,68,68,0.7)','rgba(139,92,246,0.7)','rgba(6,182,212,0.7)'], 0, count($paymentMethods['values']))) !!},
                    borderRadius: 6, borderWidth: 0,
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...tooltipStyle, callbacks: { label: ctx => '$' + ctx.parsed.x.toLocaleString(undefined,{minimumFractionDigits:2}) } }
                },
                scales: {
                    x: { ticks: { color: textColor, callback: v => '$' + (v>=1000?(v/1000).toFixed(0)+'k':v), font: { size: 10 } }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor, font: { size: 11, weight: 'bold' } }, grid: { display: false } }
                }
            }
        });
        @endif
    });
    </script>
</x-filament-panels::page>
