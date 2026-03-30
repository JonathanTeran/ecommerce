<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Tenant;

class TenantDemoDataBuilder
{
    public function build(Tenant $tenant): void
    {
        app()->instance('current_tenant', $tenant);

        $this->createBrands($tenant);
        $this->createCategories($tenant);
        $this->createProducts($tenant);
        $this->createCoupons($tenant);
    }

    private function createBrands(Tenant $tenant): void
    {
        $brands = ['TechPro', 'StyleMax', 'HomeElite', 'SmartGear', 'EcoPlus'];

        foreach ($brands as $name) {
            Brand::firstOrCreate(
                ['name' => $name, 'tenant_id' => $tenant->id],
                ['slug' => \Illuminate\Support\Str::slug($name), 'is_active' => true]
            );
        }
    }

    private function createCategories(Tenant $tenant): void
    {
        $categories = [
            ['name' => 'Tecnologia', 'description' => 'Laptops, tablets y dispositivos electronicos'],
            ['name' => 'Accesorios', 'description' => 'Fundas, cables, cargadores y mas'],
            ['name' => 'Hogar', 'description' => 'Productos para el hogar inteligente'],
            ['name' => 'Audio', 'description' => 'Audifonos, parlantes y sistemas de sonido'],
            ['name' => 'Gaming', 'description' => 'Todo para gamers: perifericos y componentes'],
            ['name' => 'Oficina', 'description' => 'Impresoras, monitores y suministros'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($cat['name']), 'tenant_id' => $tenant->id],
                [
                    'name' => ['es' => $cat['name'], 'en' => $cat['name']],
                    'description' => ['es' => $cat['description'], 'en' => $cat['description']],
                    'is_active' => true,
                ]
            );
        }
    }

    private function createProducts(Tenant $tenant): void
    {
        $categories = Category::where('tenant_id', $tenant->id)->get()->keyBy('slug');
        $brands = Brand::where('tenant_id', $tenant->id)->get();

        $products = [
            ['name' => 'Laptop Ultrabook Pro 15', 'price' => 899.99, 'compare_price' => 1099.99, 'cat' => 'tecnologia', 'desc' => 'Laptop ultradelgada con procesador de ultima generacion, 16GB RAM, 512GB SSD.', 'sku' => 'DEMO-001', 'featured' => true, 'new' => true],
            ['name' => 'Tablet Smart 10 pulgadas', 'price' => 349.99, 'compare_price' => 449.99, 'cat' => 'tecnologia', 'desc' => 'Tablet con pantalla HD, ideal para trabajo y entretenimiento.', 'sku' => 'DEMO-002', 'featured' => true, 'new' => false],
            ['name' => 'Audifonos Bluetooth Premium', 'price' => 79.99, 'compare_price' => 129.99, 'cat' => 'audio', 'desc' => 'Audifonos inalambricos con cancelacion de ruido activa y 30h de bateria.', 'sku' => 'DEMO-003', 'featured' => true, 'new' => true],
            ['name' => 'Monitor Curvo 27 QHD', 'price' => 449.99, 'compare_price' => 549.99, 'cat' => 'oficina', 'desc' => 'Monitor curvo de 27 pulgadas, resolucion QHD, ideal para trabajo y gaming.', 'sku' => 'DEMO-004', 'featured' => false, 'new' => true],
            ['name' => 'Teclado Mecanico RGB', 'price' => 89.99, 'compare_price' => null, 'cat' => 'gaming', 'desc' => 'Teclado mecanico con switches Cherry MX e iluminacion RGB personalizable.', 'sku' => 'DEMO-005', 'featured' => true, 'new' => false],
            ['name' => 'Mouse Ergonomico Wireless', 'price' => 45.99, 'compare_price' => 59.99, 'cat' => 'accesorios', 'desc' => 'Mouse inalambrico ergonomico con sensor de alta precision.', 'sku' => 'DEMO-006', 'featured' => false, 'new' => false],
            ['name' => 'Parlante Portatil Waterproof', 'price' => 59.99, 'compare_price' => 79.99, 'cat' => 'audio', 'desc' => 'Parlante bluetooth resistente al agua con 20 horas de bateria.', 'sku' => 'DEMO-007', 'featured' => true, 'new' => true],
            ['name' => 'Cargador Rapido USB-C 65W', 'price' => 35.99, 'compare_price' => null, 'cat' => 'accesorios', 'desc' => 'Cargador universal USB-C compatible con laptops, tablets y celulares.', 'sku' => 'DEMO-008', 'featured' => false, 'new' => false],
            ['name' => 'Webcam Full HD con Microfono', 'price' => 69.99, 'compare_price' => 89.99, 'cat' => 'oficina', 'desc' => 'Webcam 1080p con microfono incorporado para videoconferencias.', 'sku' => 'DEMO-009', 'featured' => false, 'new' => true],
            ['name' => 'Hub USB-C 7 en 1', 'price' => 49.99, 'compare_price' => 69.99, 'cat' => 'accesorios', 'desc' => 'Hub multipuerto con HDMI, USB 3.0, SD card y carga PD.', 'sku' => 'DEMO-010', 'featured' => true, 'new' => false],
            ['name' => 'Lampara LED Escritorio Smart', 'price' => 42.99, 'compare_price' => null, 'cat' => 'hogar', 'desc' => 'Lampara de escritorio con control tactil, temperatura de color ajustable.', 'sku' => 'DEMO-011', 'featured' => false, 'new' => true],
            ['name' => 'Mochila Laptop Antirrobo', 'price' => 55.99, 'compare_price' => 79.99, 'cat' => 'accesorios', 'desc' => 'Mochila para laptop de hasta 15.6" con cierre antirrobo y puerto USB.', 'sku' => 'DEMO-012', 'featured' => true, 'new' => false],
        ];

        foreach ($products as $p) {
            $category = $categories[$p['cat']] ?? $categories->first();
            $brand = $brands->random();

            Product::firstOrCreate(
                ['sku' => $p['sku'], 'tenant_id' => $tenant->id],
                [
                    'name' => ['es' => $p['name'], 'en' => $p['name']],
                    'slug' => \Illuminate\Support\Str::slug($p['name']),
                    'description' => ['es' => $p['desc'], 'en' => $p['desc']],
                    'short_description' => ['es' => \Illuminate\Support\Str::limit($p['desc'], 80), 'en' => \Illuminate\Support\Str::limit($p['desc'], 80)],
                    'price' => $p['price'],
                    'compare_price' => $p['compare_price'],
                    'cost' => round($p['price'] * 0.6, 2),
                    'quantity' => rand(10, 100),
                    'low_stock_threshold' => 5,
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'is_active' => true,
                    'is_featured' => $p['featured'],
                    'is_new' => $p['new'],
                ]
            );
        }
    }

    private function createCoupons(Tenant $tenant): void
    {
        $coupons = [
            ['code' => 'BIENVENIDO10', 'type' => 'percentage', 'value' => 10, 'desc' => '10% de descuento en tu primera compra'],
            ['code' => 'VERANO20', 'type' => 'percentage', 'value' => 20, 'desc' => '20% de descuento en temporada de verano'],
            ['code' => 'ENVIOGRATIS', 'type' => 'free_shipping', 'value' => 0, 'desc' => 'Envio gratuito sin minimo de compra'],
        ];

        foreach ($coupons as $c) {
            Coupon::firstOrCreate(
                ['code' => $c['code'], 'tenant_id' => $tenant->id],
                [
                    'name' => $c['code'],
                    'description' => $c['desc'],
                    'type' => $c['type'],
                    'value' => $c['value'],
                    'min_purchase_amount' => 0,
                    'max_uses' => 100,
                    'used_count' => 0,
                    'starts_at' => now(),
                    'expires_at' => now()->addMonths(3),
                    'is_active' => true,
                ]
            );
        }
    }
}
