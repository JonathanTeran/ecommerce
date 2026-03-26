<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('social_provider')->nullable()->after('password');
            $table->string('social_id')->nullable()->after('social_provider');
            $table->text('social_avatar')->nullable()->after('social_id');

            $table->unique(['social_provider', 'social_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['social_provider', 'social_id']);
            $table->dropColumn(['social_provider', 'social_id', 'social_avatar']);
        });
    }
};
