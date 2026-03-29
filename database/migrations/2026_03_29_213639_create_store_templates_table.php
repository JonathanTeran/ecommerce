<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category', 50)->default('general');
            $table->string('preview_image')->nullable();
            $table->string('preview_desktop')->nullable();
            $table->string('preview_mobile')->nullable();
            $table->string('assets_path')->nullable();
            $table->string('css_file')->nullable();
            $table->json('color_scheme')->nullable();
            $table->json('fonts')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add store_template_id to tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('store_template_id')->nullable()->after('theme_template')->constrained('store_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_template_id');
        });
        Schema::dropIfExists('store_templates');
    }
};
