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
        Schema::create('global_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider'); // ej: 'stripe', 'mercado_pago', 'fedex'
            $table->string('type'); // ej: 'payment', 'logistics', 'erp'
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_setup')->default(true);
            $table->text('description')->nullable();
            $table->json('global_credentials')->nullable();
            $table->json('supported_countries')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_integrations');
    }
};
