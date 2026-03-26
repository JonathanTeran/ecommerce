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
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('number')->unique();
                $table->string('status')->default('pending'); // new, processing, shipped, delivered, cancelled
                $table->string('payment_status')->default('pending'); // pending, paid, failed, refunded
                $table->string('payment_method')->nullable();
                $table->string('transaction_id')->nullable();

                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('tax_amount', 10, 2)->default(0);
                $table->decimal('shipping_amount', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);

                $table->text('notes')->nullable();
                $table->json('shipping_address')->nullable();
                $table->json('billing_address')->nullable();

                $table->timestamp('placed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('product_variant_id')->nullable(); // assuming simple ID for now if table doesn't exist
                $table->string('product_name'); // Snapshot of name
                $table->integer('quantity');
                $table->decimal('price', 10, 2);
                $table->decimal('total', 10, 2);
                $table->json('options')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
