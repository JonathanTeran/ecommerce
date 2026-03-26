<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('is_default', true)->first();

        if (! $tenant) {
            $this->command->warn('No se encontro un tenant por defecto. Marque un tenant como is_default=true.');

            return;
        }

        app()->instance('current_tenant', $tenant);
        $categories = [
            'COMPUTADORAS' => [
                'Notebook - Laptop',
                'All In One',
                'PC - CPU',
            ],
            'REPUESTOS PARA LAPTOP' => [
                'Pantallas' => [
                    '8.9" a 10.1"',
                    '11.6"',
                    '13.3"',
                    '14.0"',
                    '15.4"',
                    '15.6"',
                    '17.3"',
                    'Ensamble completo de pantalla',
                ],
                'Cargadores',
                'Baterías',
                'Pines de carga',
                'Coolers',
                'Teclados',
                'Bisagras',
                'Carcasas',
                'Flex de video',
            ],
            'COMPONENTES PC' => [
                'Almacenamiento' => [
                    'Pen Drive', 'Disco Duro Externo', 'Memoria Micro SD', 'Disco Duro Sólido (SSD)',
                ],
                'Impresión' => [
                    'Cintas', 'Impresoras', 'Repuestos', 'Tinta', 'Toners',
                ],
                'Hardware' => [
                    'Case para PC', 'Enclosure', 'Fuentes de poder', 'Memorias (Laptop/PC)', 'Motherboards', 'Procesadores', 'Tarjetas de video',
                ],
                'Conectividad' => [
                    'Routers', 'Switch', 'Extensor de rango', 'Access Point',
                ],
                'Periféricos' => [
                    'Parlantes', 'Audífonos', 'Mouse', 'Teclados para PC', 'UPS', 'Proyectores',
                ],
            ],
            'VIDEO Y VIGILANCIA' => [
                'Cámara Web',
                'Cámara de Seguridad',
                'Accesorios de Cámaras',
                'Biométricos',
            ],
            'SOFTWARE Y SERVICIOS' => [
                'Limpieza física',
                'Reparaciones electrónicas',
                'Reballing',
                'Antivirus',
                'Licencias Windows',
                'Reconstrucción de carcasas',
                'Reparación de bisagras',
            ],
            'GENERAL' => [
                'Mochilas para laptop',
                'Seguros para pantalla',
                'Tira adhesiva de pantalla',
                'Caddy HD',
                'Adaptadores varios',
                'Reguladores',
            ],
        ];

        foreach ($categories as $mainCategory => $subCategories) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($mainCategory)],
                ['name' => $mainCategory, 'is_active' => true, 'is_featured' => true]
            );

            foreach ($subCategories as $key => $value) {
                if (is_array($value)) {
                    $subParent = Category::firstOrCreate(
                        ['slug' => Str::slug($key), 'parent_id' => $parent->id],
                        ['name' => $key, 'is_active' => true]
                    );

                    foreach ($value as $subSubCategory) {
                        Category::firstOrCreate(
                            ['slug' => Str::slug($subSubCategory), 'parent_id' => $subParent->id],
                            ['name' => $subSubCategory, 'is_active' => true]
                        );
                    }
                } else {
                    Category::firstOrCreate(
                        ['slug' => Str::slug($value), 'parent_id' => $parent->id],
                        ['name' => $value, 'is_active' => true]
                    );
                }
            }
        }
    }
}
