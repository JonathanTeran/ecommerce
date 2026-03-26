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
            if (! Schema::hasColumn('orders', 'payment_method_id')) {
                $table->foreignId('payment_method_id')->nullable()->after('user_id')->constrained('payment_methods')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('orders', 'surcharge_amount')) {
                $table->decimal('surcharge_amount', 10, 2)->default(0)->after('tax_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn(['payment_method_id', 'payment_proof_path', 'surcharge_amount']);
        });
    }
};
