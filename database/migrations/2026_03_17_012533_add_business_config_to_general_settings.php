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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('USD')->after('tax_rate');
            $table->string('currency_symbol', 5)->default('$')->after('currency_code');
            $table->unsignedSmallInteger('quotation_validity_days')->default(15)->after('currency_symbol');
            $table->string('quotation_prefix', 10)->default('COT')->after('quotation_validity_days');
            $table->unsignedSmallInteger('cart_expiration_days')->default(30)->after('quotation_prefix');
            $table->unsignedSmallInteger('abandoned_cart_reminder_hours')->default(24)->after('cart_expiration_days');
            $table->unsignedSmallInteger('low_stock_threshold')->default(5)->after('abandoned_cart_reminder_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'currency_symbol',
                'quotation_validity_days',
                'quotation_prefix',
                'cart_expiration_days',
                'abandoned_cart_reminder_hours',
                'low_stock_threshold',
            ]);
        });
    }
};
