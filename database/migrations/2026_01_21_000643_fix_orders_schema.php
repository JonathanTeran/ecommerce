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
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'number') && ! Schema::hasColumn('orders', 'order_number')) {
                $table->renameColumn('number', 'order_number');
            } elseif (! Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->unique()->after('user_id');
            }

            if (! Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('total');
            }
            if (! Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('discount_amount');
            }
            if (! Schema::hasColumn('orders', 'shipping_method')) {
                $table->string('shipping_method')->nullable()->after('shipping_address');
            }
            if (! Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('shipping_method');
            }
            if (! Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable();
            }
            if (! Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable();
            }
            if (! Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }
            if (! Schema::hasColumn('orders', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable();
            }
            if (! Schema::hasColumn('orders', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }
            if (! Schema::hasColumn('orders', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }
        });
    }

    public function down(): void
    {
        // simplistic down
        Schema::table('orders', function (Blueprint $table) {
            // Revert changes if needed
        });
    }
};
