<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->json('navbar_config')->nullable();
            $table->json('footer_config')->nullable();
            $table->json('typography_config')->nullable();
            $table->text('custom_css')->nullable();
            $table->json('social_links')->nullable();
            $table->json('product_page_config')->nullable();
            $table->json('shop_page_config')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'navbar_config',
                'footer_config',
                'typography_config',
                'custom_css',
                'social_links',
                'product_page_config',
                'shop_page_config',
            ]);
        });
    }
};
