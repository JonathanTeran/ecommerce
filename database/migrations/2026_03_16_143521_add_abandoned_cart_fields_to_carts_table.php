<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('expires_at');
            $table->unsignedTinyInteger('reminder_count')->default(0)->after('reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['reminder_sent_at', 'reminder_count']);
        });
    }
};
