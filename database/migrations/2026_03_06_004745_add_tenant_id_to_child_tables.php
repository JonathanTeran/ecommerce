<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['order_status_histories', 'product_variants', 'stock_transfer_items'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['order_status_histories', 'product_variants', 'stock_transfer_items'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropForeign(['tenant_id']);
                    $blueprint->dropColumn('tenant_id');
                });
            }
        }
    }
};
