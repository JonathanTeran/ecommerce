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
        // Ensure table exists first if not already present (failsafe)
        if (! Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('session_id')->nullable()->index();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->integer('quantity')->default(1);
                $table->timestamps();
            });
        }

        // Add additional columns if missing
        Schema::table('carts', function (Blueprint $table) {
            if (! Schema::hasColumn('carts', 'coupon_id')) {
                // Assuming coupons table might not exist yet, make it nullable integer or simple index
                $table->foreignId('coupon_id')->nullable()->after('session_id');
            }
            if (! Schema::hasColumn('carts', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('coupon_id');
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'variant_id')) {
                $table->foreignId('variant_id')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('cart_items', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('quantity');
            }
            if (! Schema::hasColumn('cart_items', 'options')) {
                $table->json('options')->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
