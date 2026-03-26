<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillTenantId extends Command
{
    protected $signature = 'tenant:backfill {--tenant= : Tenant ID to assign (defaults to first tenant)}';

    protected $description = 'Backfill tenant_id on all tenant-scoped tables where it is NULL';

    public function handle(): int
    {
        $tenantId = $this->option('tenant') ?? Tenant::first()?->id;

        if (! $tenantId) {
            $this->error('No tenant found. Create a tenant first.');

            return self::FAILURE;
        }

        $tenant = Tenant::find($tenantId);
        $this->info("Backfilling tenant_id = {$tenantId} ({$tenant->name}) on all scoped tables...");

        $tables = [
            'addresses',
            'banners',
            'brands',
            'carts',
            'cart_items',
            'categories',
            'coupons',
            'general_settings',
            'inventory_movements',
            'newsletter_subscribers',
            'orders',
            'order_items',
            'order_status_histories',
            'payments',
            'payment_methods',
            'products',
            'product_variants',
            'quotations',
            'quotation_items',
            'reviews',
            'stock_alerts',
            'stock_transfers',
            'stock_transfer_items',
            'warehouse_locations',
            'wishlists',
        ];

        $totalUpdated = 0;

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            $count = DB::table($table)
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);

            if ($count > 0) {
                $this->line("  {$table}: {$count} rows updated");
            }

            $totalUpdated += $count;
        }

        $this->info("Done. {$totalUpdated} total rows updated.");

        return self::SUCCESS;
    }
}
