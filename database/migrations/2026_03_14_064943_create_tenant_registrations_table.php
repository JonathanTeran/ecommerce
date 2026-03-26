<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('store_name');
            $table->string('slug')->unique();
            $table->foreignId('plan_id')->constrained();
            $table->string('owner_name');
            $table->string('owner_email')->unique();
            $table->string('owner_phone')->nullable();
            $table->string('password');
            $table->string('country')->default('Ecuador');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_token')->nullable();
            $table->foreignId('provisioned_tenant_id')->nullable()->constrained('tenants');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_registrations');
    }
};
