<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that need tenant_id.
     *
     * @var string[]
     */
    protected array $tables = [
        'users',
        'products',
        'categories',
        'brands',
        'orders',
        'order_items',
        'coupons',
        'banners',
        'reviews',
        'payment_methods',
        'inventory_movements',
        'carts',
        'cart_items',
        'wishlists',
        'addresses',
        'newsletter_subscribers',
        'general_settings',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
                    $table->index('tenant_id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    $blueprint->dropConstrainedForeignId('tenant_id');
                });
            }
        }
    }
};
