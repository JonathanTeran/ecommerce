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
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('user_id');
            $table->foreignId('warehouse_location_id')->nullable()->after('notes')->constrained()->nullOnDelete();
            $table->string('batch_number', 50)->nullable()->after('warehouse_location_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('warehouse_location_id')->nullable()->after('low_stock_threshold')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_location_id');
            $table->dropColumn(['notes', 'batch_number']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_location_id');
        });
    }
};
