<?php

namespace App\Filament\Pages;

use App\Enums\Module;
use App\Enums\OrderStatus;
use App\Enums\QuotationStatus;
use App\Enums\ReturnStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\Quotation;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ReportsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $title = 'Reportes';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.reports-page';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            return false;
        }

        return $tenant->hasModule(Module::Reports);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function updatedDateFrom(): void
    {
        // Livewire reactivity
    }

    public function updatedDateTo(): void
    {
        // Livewire reactivity
    }

    protected function getDateRange(): array
    {
        return [
            Carbon::parse($this->dateFrom)->startOfDay(),
            Carbon::parse($this->dateTo)->endOfDay(),
        ];
    }

    public function getSalesReport(): array
    {
        [$from, $to] = $this->getDateRange();

        $orders = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders_count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRevenue = $orders->sum('revenue');
        $totalOrders = $orders->sum('orders_count');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'avg_order_value' => $avgOrderValue,
            'daily' => $orders,
        ];
    }

    public function getTopProductsReport(): \Illuminate\Support\Collection
    {
        [$from, $to] = $this->getDateRange();

        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNull('orders.deleted_at')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
    }

    public function getTopCustomersReport(): \Illuminate\Support\Collection
    {
        [$from, $to] = $this->getDateRange();

        return Order::query()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->select(
                'users.name',
                'users.email',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('SUM(orders.total) as total_spent')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();
    }

    public function getCategoryPerformance(): \Illuminate\Support\Collection
    {
        [$from, $to] = $this->getDateRange();

        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNull('orders.deleted_at')
            ->select(
                'categories.name',
                DB::raw('SUM(order_items.quantity) as total_units'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function getPaymentMethodBreakdown(): \Illuminate\Support\Collection
    {
        [$from, $to] = $this->getDateRange();

        return Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('payment_method')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function getOrderConversionRate(): array
    {
        [$from, $to] = $this->getDateRange();

        $totalQuotations = Quotation::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $convertedQuotations = Quotation::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', QuotationStatus::Converted)
            ->count();

        $rate = $totalQuotations > 0 ? ($convertedQuotations / $totalQuotations) * 100 : 0;

        return [
            'total' => $totalQuotations,
            'converted' => $convertedQuotations,
            'rate' => round($rate, 1),
        ];
    }

    public function getAverageOrderValue(): float
    {
        [$from, $to] = $this->getDateRange();

        $result = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('AVG(total) as avg_value')
            ->value('avg_value');

        return round((float) $result, 2);
    }

    public function getOrderStatusBreakdown(): \Illuminate\Support\Collection
    {
        [$from, $to] = $this->getDateRange();

        return Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                $statusValue = $item->status instanceof OrderStatus ? $item->status->value : $item->status;
                $status = OrderStatus::tryFrom($statusValue);

                return (object) [
                    'status' => $status?->getLabel() ?? $statusValue,
                    'count' => $item->count,
                    'color' => $status?->getColor() ?? 'gray',
                ];
            });
    }

    public function getReturnsReport(): array
    {
        [$from, $to] = $this->getDateRange();

        $total = ProductReturn::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $pending = ProductReturn::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', ReturnStatus::Requested)
            ->count();

        $refunded = ProductReturn::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', ReturnStatus::Refunded)
            ->sum('refund_amount');

        $byReason = ProductReturn::query()
            ->whereBetween('created_at', [$from, $to])
            ->select('reason', DB::raw('COUNT(*) as count'))
            ->groupBy('reason')
            ->orderByDesc('count')
            ->get();

        return [
            'total' => $total,
            'pending' => $pending,
            'refunded_amount' => $refunded,
            'by_reason' => $byReason,
        ];
    }

    public function getInventoryReport(): array
    {
        $outOfStock = Product::query()
            ->where('is_active', true)
            ->where('quantity', '<=', 0)
            ->count();

        $lowStock = Product::query()
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->count();

        $totalValue = Product::query()
            ->where('is_active', true)
            ->selectRaw('SUM(quantity * COALESCE(cost, price)) as total_value')
            ->value('total_value');

        $topLowStock = Product::query()
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->orderBy('quantity')
            ->limit(10)
            ->get(['name', 'sku', 'quantity', 'low_stock_threshold']);

        return [
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock,
            'total_value' => (float) $totalValue,
            'top_low_stock' => $topLowStock,
        ];
    }

    public function getSupportReport(): array
    {
        [$from, $to] = $this->getDateRange();

        $total = SupportTicket::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $open = SupportTicket::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $resolved = SupportTicket::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', 'resolved')
            ->count();

        $driver = DB::getDriverName();
        $avgResolutionHours = SupportTicket::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('resolved_at')
            ->selectRaw($driver === 'sqlite'
                ? 'AVG((julianday(resolved_at) - julianday(created_at)) * 24) as avg_hours'
                : 'AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        return [
            'total' => $total,
            'open' => $open,
            'resolved' => $resolved,
            'avg_resolution_hours' => round((float) $avgResolutionHours, 1),
        ];
    }

    public function getRevenueByMonth(): \Illuminate\Support\Collection
    {
        $driver = DB::getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at) as month"
            : "DATE_FORMAT(created_at, '%Y-%m') as month";

        return Order::query()
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw("$monthExpr, SUM(total) as revenue, COUNT(*) as orders_count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function getNewVsReturningCustomers(): array
    {
        [$from, $to] = $this->getDateRange();

        $totalCustomers = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->distinct('user_id')
            ->count('user_id');

        $returningCustomers = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('user_id', function ($q) use ($from) {
                $q->select('user_id')
                    ->from('orders')
                    ->where('created_at', '<', $from)
                    ->distinct();
            })
            ->distinct('user_id')
            ->count('user_id');

        $newCustomers = $totalCustomers - $returningCustomers;

        return [
            'total' => $totalCustomers,
            'new' => $newCustomers,
            'returning' => $returningCustomers,
        ];
    }
}
