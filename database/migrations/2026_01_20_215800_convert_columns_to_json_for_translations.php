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
        // Convert existing data to JSON structure before changing column types
        // This prevents data loss or constraints errors
        $driver = DB::connection()->getDriverName();
        $locale = config('app.locale', 'es');

        $tables = [
            'products' => ['name', 'description', 'short_description', 'specifications', 'meta_title', 'meta_description'],
            'categories' => ['name', 'description'],
            'brands' => ['description'],
        ];

        foreach ($tables as $table => $columns) {
            foreach ($columns as $column) {
                // Ensure column exists
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                if ($driver === 'sqlite') {
                    // SQLite concatenation
                    // Check if it already looks like JSON (starts with { or [) to avoid double encoding
                    DB::statement("UPDATE {$table} SET {$column} = '{\"' || '{$locale}' || '\": \"' || REPLACE({$column}, '\"', '\\\"') || '\"}' WHERE {$column} IS NOT NULL AND {$column} NOT LIKE '{%' AND {$column} NOT LIKE '[%'");
                } elseif ($driver === 'mysql') {
                    // MySQL JSON_OBJECT
                    DB::statement("UPDATE {$table} SET {$column} = JSON_OBJECT('{$locale}', {$column}) WHERE {$column} IS NOT NULL AND {$column} NOT LIKE '{%' AND {$column} NOT LIKE '[%'");
                } else {
                    // Fallback assuming simple string to JSON string for other drivers provided they support standard SQL or similar
                    // For now, focus on SQLite/MySQL
                }
            }
        }

        // Products
        Schema::table('products', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
            $table->json('short_description')->nullable()->change();
            $table->json('specifications')->nullable()->change();
            $table->json('meta_title')->nullable()->change();
            $table->json('meta_description')->nullable()->change();
        });

        // Categories
        Schema::table('categories', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });

        // Brands
        Schema::table('brands', function (Blueprint $table) {
            $table->json('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting JSON columns back to String/Text is complex without data loss if data is already JSON.
        // For development purposes, we assume we can revert to string (taking the first locale or similar),
        // but typically down migrations for type changes with data conversion are risky.
        // Here we just revert type definition.

        Schema::table('products', function (Blueprint $table) {
            $table->string('name')->change();
            $table->longText('description')->nullable()->change();
            $table->text('short_description')->nullable()->change();
            $table->json('specifications')->nullable()->change(); // specifications was likely already json or text
            $table->string('meta_title')->nullable()->change();
            $table->text('meta_description')->nullable()->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->change();
            $table->longText('description')->nullable()->change();
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
        });
    }
};
