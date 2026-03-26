<?php

namespace App\Filament\Pages;

use App\Enums\Module;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockAlert;
use Filament\Pages\Page;

class InventoryDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Inventario';

    protected static ?string $title = 'Dashboard de Inventario';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.inventory-dashboard';

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

        return $tenant->hasModule(Module::Inventory);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getStats(): array
    {
        return [
            'total_in_stock' => Product::where('quantity', '>', 0)->count(),
            'low_stock' => Product::lowStock()->count(),
            'out_of_stock' => Product::outOfStock()->count(),
            'inventory_value' => Product::where('quantity', '>', 0)
                ->selectRaw('SUM(quantity * cost) as total_value')
                ->value('total_value') ?? 0,
            'pending_alerts' => StockAlert::pending()->count(),
        ];
    }

    public function getLowStockProducts()
    {
        return Product::query()
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->orderBy('quantity')
            ->limit(10)
            ->get(['id', 'name', 'sku', 'quantity', 'low_stock_threshold', 'cost']);
    }

    public function getOutOfStockProducts()
    {
        return Product::query()
            ->where('quantity', '<=', 0)
            ->orderByDesc('sales_count')
            ->limit(10)
            ->get(['id', 'name', 'sku', 'quantity', 'sales_count']);
    }

    public function getRecentMovements()
    {
        return InventoryMovement::with(['product', 'user'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function getPendingAlerts()
    {
        return StockAlert::with('product')
            ->pending()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }
}
