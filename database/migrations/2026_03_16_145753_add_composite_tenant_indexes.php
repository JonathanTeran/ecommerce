<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            'orders' => ['tenant_id', 'user_id'],
            'products' => ['tenant_id', 'category_id'],
            'reviews' => ['tenant_id', 'product_id'],
            'wishlists' => ['tenant_id', 'user_id'],
            'carts' => ['tenant_id', 'user_id'],
            'quotations' => ['tenant_id', 'user_id'],
            'support_tickets' => ['tenant_id', 'user_id'],
            'returns' => ['tenant_id', 'user_id'],
            'inventory_movements' => ['tenant_id', 'product_id'],
            'newsletter_subscribers' => ['tenant_id', 'email'],
        ];

        foreach ($indexes as $table => $columns) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $columns[0]) && Schema::hasColumn($table, $columns[1])) {
                $indexName = $table . '_' . implode('_', $columns) . '_index';

                Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
            }
        }

        // Orders: composite for status filtering
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'tenant_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['tenant_id', 'status', 'created_at'], 'orders_tenant_status_created_index');
            });
        }
    }

    public function down(): void
    {
        $indexes = [
            'orders' => 'orders_tenant_id_user_id_index',
            'products' => 'products_tenant_id_category_id_index',
            'reviews' => 'reviews_tenant_id_product_id_index',
            'wishlists' => 'wishlists_tenant_id_user_id_index',
            'carts' => 'carts_tenant_id_user_id_index',
            'quotations' => 'quotations_tenant_id_user_id_index',
            'support_tickets' => 'support_tickets_tenant_id_user_id_index',
            'returns' => 'returns_tenant_id_user_id_index',
            'inventory_movements' => 'inventory_movements_tenant_id_product_id_index',
            'newsletter_subscribers' => 'newsletter_subscribers_tenant_id_email_index',
        ];

        foreach ($indexes as $table => $indexName) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_tenant_status_created_index');
            });
        }
    }
};
