<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('mail_from_name')->nullable()->after('sri_next_sequence');
            $table->string('mail_from_address')->nullable()->after('mail_from_name');
            $table->string('smtp_host')->nullable()->after('mail_from_address');
            $table->unsignedSmallInteger('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_username')->nullable()->after('smtp_port');
            $table->text('smtp_password')->nullable()->after('smtp_username');
            $table->string('smtp_encryption')->nullable()->after('smtp_password');
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'mail_from_name',
                'mail_from_address',
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
            ]);
        });
    }
};
