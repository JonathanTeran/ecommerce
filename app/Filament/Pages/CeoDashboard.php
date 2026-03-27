<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CeoDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $title = 'Dashboard Ejecutivo';

    protected static ?string $navigationLabel = 'Dashboard CEO';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.ceo-dashboard';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    protected function resolveTranslatableName(mixed $value): string
    {
        if (! is_string($value)) {
            return (string) $value;
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return $value;
        }

        $locale = app()->getLocale();

        return $decoded[$locale] ?? $decoded['es'] ?? $decoded['en'] ?? reset($decoded) ?: $value;
    }

    // ═══════════════════════════════════════════
    // KPIs PRINCIPALES
    // ═══════════════════════════════════════════

    public function getRevenueKpis(): array
    {
        $now = Carbon::now();

        $thisMonth = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('total');

        $lastMonth = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])
            ->sum('total');

        $thisYear = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])
            ->sum('total');

        $today = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->whereDate('created_at', today())
            ->sum('total');

        $yesterday = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->whereDate('created_at', today()->subDay())
            ->sum('total');

        $growthPct = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : ($thisMonth > 0 ? 100 : 0);

        return [
            'today' => $today,
            'yesterday' => $yesterday,
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'this_year' => $thisYear,
            'growth_pct' => round($growthPct, 1),
        ];
    }

    public function getOrderKpis(): array
    {
        $now = Carbon::now();

        $thisMonth = Order::whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count();
        $lastMonth = Order::whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])->count();
        $pending = Order::where('status', OrderStatus::PENDING)->count();
        $processing = Order::whereIn('status', [OrderStatus::CONFIRMED, OrderStatus::PROCESSING])->count();

        $avgOrderValue = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->avg('total') ?? 0;

        $growthPct = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : ($thisMonth > 0 ? 100 : 0);

        return [
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'growth_pct' => round($growthPct, 1),
            'pending' => $pending,
            'processing' => $processing,
            'avg_order_value' => round($avgOrderValue, 2),
        ];
    }

    public function getCustomerKpis(): array
    {
        $now = Carbon::now();

        $totalCustomers = User::count();
        $newThisMonth = User::whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count();

        $customersWithOrders = Order::distinct('user_id')->count('user_id');
        $conversionRate = $totalCustomers > 0 ? ($customersWithOrders / $totalCustomers) * 100 : 0;

        $repeatCustomers = DB::table('orders')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $repeatRate = $customersWithOrders > 0 ? ($repeatCustomers / $customersWithOrders) * 100 : 0;

        return [
            'total' => $totalCustomers,
            'new_this_month' => $newThisMonth,
            'conversion_rate' => round($conversionRate, 1),
            'repeat_rate' => round($repeatRate, 1),
        ];
    }

    public function getInventoryKpis(): array
    {
        $totalProducts = Product::where('is_active', true)->count();
        $outOfStock = Product::where('is_active', true)->where('quantity', '<=', 0)->count();
        $lowStock = Product::where('is_active', true)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('quantity', '>', 0)
            ->count();
        $totalValue = Product::where('is_active', true)
            ->selectRaw('SUM(quantity * COALESCE(cost, price)) as total')
            ->value('total') ?? 0;

        return [
            'total_products' => $totalProducts,
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock,
            'inventory_value' => round($totalValue, 2),
        ];
    }

    // ═══════════════════════════════════════════
    // DATOS PARA GRAFICOS
    // ═══════════════════════════════════════════

    public function getMonthlyRevenue(): array
    {
        $driver = DB::getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at) as month"
            : "DATE_FORMAT(created_at, '%Y-%m') as month";

        $data = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->selectRaw("$monthExpr, SUM(total) as revenue, COUNT(*) as orders")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }

        $revenues = [];
        $orders = [];
        foreach ($months as $m) {
            $found = $data->firstWhere('month', $m);
            $revenues[] = $found ? round((float) $found->revenue, 2) : 0;
            $orders[] = $found ? (int) $found->orders : 0;
        }

        $labels = array_map(fn ($m) => Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y'), $months);

        return compact('labels', 'revenues', 'orders');
    }

    public function getDailyRevenue7Days(): array
    {
        $driver = DB::getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m-%d', created_at) as day"
            : 'DATE(created_at) as day';

        $data = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw("$dateExpr, SUM(total) as revenue, COUNT(*) as orders")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $labels = [];
        $revenues = [];
        $orders = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->translatedFormat('D');
            $found = $data->firstWhere('day', $day);
            $revenues[] = $found ? round((float) $found->revenue, 2) : 0;
            $orders[] = $found ? (int) $found->orders : 0;
        }

        return compact('labels', 'revenues', 'orders');
    }

    public function getOrderStatusDistribution(): array
    {
        $data = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = [
            'pending' => 'Pendientes',
            'confirmed' => 'Confirmados',
            'processing' => 'Procesando',
            'shipped' => 'Enviados',
            'delivered' => 'Entregados',
            'cancelled' => 'Cancelados',
            'refunded' => 'Reembolsados',
            'on_hold' => 'En espera',
        ];

        $statusColors = [
            'pending' => '#f59e0b',
            'confirmed' => '#3b82f6',
            'processing' => '#8b5cf6',
            'shipped' => '#06b6d4',
            'delivered' => '#10b981',
            'cancelled' => '#ef4444',
            'refunded' => '#f97316',
            'on_hold' => '#6b7280',
        ];

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($data as $status => $count) {
            $labels[] = $statusLabels[$status] ?? ucfirst($status);
            $values[] = $count;
            $colors[] = $statusColors[$status] ?? '#6b7280';
        }

        return compact('labels', 'values', 'colors');
    }

    public function getCategoryRevenue(): array
    {
        $driver = DB::getDriverName();

        $data = \App\Models\OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereNull('orders.deleted_at')
            ->select(
                'categories.name',
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get()
            ->each(fn ($item) => $item->name = $this->resolveTranslatableName($item->name));

        $colors = ['#6366f1', '#8b5cf6', '#a855f7', '#c084fc', '#d8b4fe', '#e9d5ff'];

        return [
            'labels' => $data->pluck('name')->toArray(),
            'values' => $data->pluck('revenue')->map(fn ($v) => round((float) $v, 2))->toArray(),
            'colors' => array_slice($colors, 0, $data->count()),
        ];
    }

    public function getPaymentMethodBreakdown(): array
    {
        $data = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->selectRaw('COALESCE(payment_method, "otros") as method, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('method')
            ->orderByDesc('revenue')
            ->get();

        $methodLabels = [
            'payphone' => 'Payphone',
            'transfer' => 'Transferencia',
            'cash_on_delivery' => 'Contra entrega',
            'credit_card' => 'Tarjeta',
            'otros' => 'Otros',
        ];

        return [
            'labels' => $data->map(fn ($d) => $methodLabels[$d->method] ?? ucfirst($d->method))->toArray(),
            'values' => $data->pluck('revenue')->map(fn ($v) => round((float) $v, 2))->toArray(),
            'counts' => $data->pluck('count')->toArray(),
            'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
        ];
    }

    public function getTopProductsData(): \Illuminate\Support\Collection
    {
        return \App\Models\OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereNull('orders.deleted_at')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as qty'),
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->each(fn ($item) => $item->name = $this->resolveTranslatableName($item->name));
    }

    public function getRecentOrders(): \Illuminate\Support\Collection
    {
        return Order::with('user')
            ->latest()
            ->limit(8)
            ->get();
    }

    public function getWeeklyComparison(): array
    {
        $thisWeekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $thisWeek = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$thisWeekStart, now()])
            ->sum('total');

        $lastWeek = Order::where('payment_status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->sum('total');

        $thisWeekOrders = Order::whereBetween('created_at', [$thisWeekStart, now()])->count();
        $lastWeekOrders = Order::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

        $revenuePct = $lastWeek > 0 ? (($thisWeek - $lastWeek) / $lastWeek) * 100 : ($thisWeek > 0 ? 100 : 0);
        $ordersPct = $lastWeekOrders > 0 ? (($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100 : ($thisWeekOrders > 0 ? 100 : 0);

        return [
            'this_week_revenue' => $thisWeek,
            'last_week_revenue' => $lastWeek,
            'revenue_pct' => round($revenuePct, 1),
            'this_week_orders' => $thisWeekOrders,
            'last_week_orders' => $lastWeekOrders,
            'orders_pct' => round($ordersPct, 1),
        ];
    }
}
