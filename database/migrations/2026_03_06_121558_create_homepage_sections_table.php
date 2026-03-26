<?php

use App\Enums\SectionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'sort_order']);
        });

        $tenantIds = DB::table('tenants')->pluck('id');

        foreach ($tenantIds as $tenantId) {
            $this->seedDefaultSections($tenantId);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }

    private function seedDefaultSections(int $tenantId): void
    {
        $defaults = [
            ['type' => 'hero', 'name' => 'Hero Principal', 'sort_order' => 1],
            ['type' => 'category_strip', 'name' => 'Categorias Rapidas', 'sort_order' => 2],
            ['type' => 'promo_banners', 'name' => 'Banners Promocionales', 'sort_order' => 3],
            ['type' => 'cta_strip', 'name' => 'Banner CTA', 'sort_order' => 4],
            ['type' => 'product_grid', 'name' => 'Tendencias', 'sort_order' => 5],
            ['type' => 'brand_slider', 'name' => 'Slider de Marcas', 'sort_order' => 6],
            ['type' => 'value_props', 'name' => 'Propuesta de Valor', 'sort_order' => 7],
        ];

        foreach ($defaults as $section) {
            $sectionType = SectionType::from($section['type']);

            DB::table('homepage_sections')->insert([
                'tenant_id' => $tenantId,
                'type' => $section['type'],
                'name' => $section['name'],
                'sort_order' => $section['sort_order'],
                'is_active' => true,
                'config' => json_encode($sectionType->defaultConfig()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
