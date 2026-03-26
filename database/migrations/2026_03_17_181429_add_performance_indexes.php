<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('placed_at');
            $table->index('payment_method');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('views_count');
            $table->index(['tenant_id', 'is_active', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['placed_at']);
            $table->dropIndex(['payment_method']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['views_count']);
            $table->dropIndex(['tenant_id', 'is_active', 'created_at']);
        });
    }
};
