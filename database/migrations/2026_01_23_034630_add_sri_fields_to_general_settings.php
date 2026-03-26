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
            $table->string('sri_ruc', 13)->nullable();
            $table->string('sri_company_name')->nullable();
            $table->string('sri_commercial_name')->nullable();
            $table->string('sri_establishment_address')->nullable();
            $table->string('sri_signature_path')->nullable();
            $table->string('sri_signature_password')->nullable(); // Plain text or encrypted? Storing as plain text for now as per simple request, but should consider encryption
            $table->integer('sri_environment')->default(1); // 1: Test, 2: Prod

            // Additional SRI codes
            $table->string('sri_establishment_code', 3)->default('001');
            $table->string('sri_emission_point_code', 3)->default('001');
            $table->string('sri_contribution_type')->nullable(); // e.g. 'CONTRIBUYENTE RÉGIMEN RIMPE'
            $table->boolean('sri_accounting_required')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sri_ruc',
                'sri_company_name',
                'sri_commercial_name',
                'sri_establishment_address',
                'sri_signature_path',
                'sri_signature_password',
                'sri_environment',
                'sri_establishment_code',
                'sri_emission_point_code',
                'sri_contribution_type',
                'sri_accounting_required',
            ]);
        });
    }
};
