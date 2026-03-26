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
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('Programa de Puntos');
            $table->decimal('points_per_dollar', 8, 2)->default(1.00);
            $table->decimal('redemption_rate', 8, 4)->default(0.01);
            $table->unsignedInteger('minimum_redemption_points')->default(100);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_programs');
    }
};
