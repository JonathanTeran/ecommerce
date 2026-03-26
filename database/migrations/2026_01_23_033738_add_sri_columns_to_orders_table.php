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
            $table->string('sri_authorization_status')->default('pending')->after('status');
            $table->string('sri_access_key', 49)->nullable()->after('sri_authorization_status');
            $table->string('sri_xml_path')->nullable()->after('sri_access_key');
            $table->dateTime('sri_authorization_date')->nullable()->after('sri_xml_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'sri_authorization_status',
                'sri_access_key',
                'sri_xml_path',
                'sri_authorization_date',
            ]);
        });
    }
};
