<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TestTenantsSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'customer']);

        $plan = Plan::where('type', 'enterprise')->first();

        if (! $plan) {
            $this->command->warn('Plan Enterprise no encontrado. Ejecute PlanSeeder primero.');

            return;
        }

        $tenants = [
            $this->createTechStore($plan),
            $this->createFashionStore($plan),
            $this->createSportsStore($plan),
            $this->createHomeStore($plan),
            $this->createGourmetStore($plan),
        ];

        foreach ($tenants as $tenantData) {
            $this->command->info("Tenant \"{$tenantData['name']}\" creado:");
            $this->command->info("  Admin: {$tenantData['admin_email']} / password");
            $this->command->info("  Buyers: buyer1@{$tenantData['slug']}.com, buyer2@{$tenantData['slug']}.com, buyer3@{$tenantData['slug']}.com / password");
            $this->command->info("  Productos: {$tenantData['products_count']} | Categorias: {$tenantData['categories_count']} | Marcas: {$tenantData['brands_count']}");
            $this->command->newLine();
        }
    }

    private function createTechStore(Plan $plan): array
    {
        $slug = 'tech-zone';

        if (Tenant::where('slug', $slug)->exists()) {
            $this->command->warn("Tenant {$slug} ya existe, saltando...");

            return ['name' => 'TechZone', 'slug' => $slug, 'admin_email' => "admin@{$slug}.com", 'products_count' => 0, 'categories_count' => 0, 'brands_count' => 0];
        }

        $tenant = app(TenantProvisioningService::class)->provision([
            'name' => 'TechZone Ecuador',
            'slug' => $slug,
            'theme_color' => 'blue',
            'plan_id' => $plan->id,
            'admin_name' => 'Carlos Mendoza',
            'admin_email' => "admin@{$slug}.com",
            'admin_password' => 'password',
            'is_demo' => true,
        ]);

        app()->instance('current_tenant', $tenant);

        $cats = $this->createCategories($tenant->id, [
            ['name' => 'Laptops', 'slug' => 'laptops', 'prefix' => 'LAP'],
            ['name' => 'Smartphones', 'slug' => 'smartphones', 'prefix' => 'PHN'],
            ['name' => 'Accesorios', 'slug' => 'accesorios', 'prefix' => 'ACC'],
            ['name' => 'Gaming', 'slug' => 'gaming', 'prefix' => 'GAM'],
        ]);

        $brands = $this->createBrands($tenant->id, ['Apple', 'Samsung', 'Dell', 'Logitech', 'Razer']);

        $products = [
            ['name' => 'MacBook Pro 16"', 'price' => 2499.00, 'cat' => 0, 'brand' => 0, 'qty' => 15],
            ['name' => 'Dell XPS 15', 'price' => 1899.00, 'cat' => 0, 'brand' => 2, 'qty' => 20],
            ['name' => 'Samsung Galaxy S24 Ultra', 'price' => 1299.00, 'cat' => 1, 'brand' => 1, 'qty' => 50],
            ['name' => 'iPhone 16 Pro Max', 'price' => 1499.00, 'cat' => 1, 'brand' => 0, 'qty' => 35],
            ['name' => 'AirPods Pro 3', 'price' => 279.00, 'cat' => 2, 'brand' => 0, 'qty' => 100],
            ['name' => 'Logitech MX Master 3S', 'price' => 99.00, 'cat' => 2, 'brand' => 3, 'qty' => 80],
            ['name' => 'Razer DeathAdder V3', 'price' => 89.00, 'cat' => 3, 'brand' => 4, 'qty' => 60],
            ['name' => 'Razer BlackWidow V4', 'price' => 169.00, 'cat' => 3, 'brand' => 4, 'qty' => 40],
            ['name' => 'Samsung Galaxy Tab S9', 'price' => 849.00, 'cat' => 0, 'brand' => 1, 'qty' => 25],
            ['name' => 'Dell Monitor 27" 4K', 'price' => 449.00, 'cat' => 2, 'brand' => 2, 'qty' => 30],
        ];

        $this->createProducts($tenant->id, $cats, $brands, $products);
        $this->createPaymentMethods($tenant->id);
        $this->createCoupons($tenant->id, [
            ['code' => 'TECH10', 'name' => '10% en tecnologia', 'type' => 'percentage', 'value' => 10],
            ['code' => 'WELCOME50', 'name' => '$50 primera compra', 'type' => 'fixed', 'value' => 50, 'first_order_only' => true],
        ]);
        $this->createBuyers($tenant->id, $slug, 3);

        return ['name' => 'TechZone Ecuador', 'slug' => $slug, 'admin_email' => "admin@{$slug}.com",
            'products_count' => count($products), 'categories_count' => count($cats), 'brands_count' => count($brands)];
    }

    private function createFashionStore(Plan $plan): array
    {
        $slug = 'moda-elite';

        if (Tenant::where('slug', $slug)->exists()) {
            $this->command->warn("Tenant {$slug} ya existe, saltando...");

            return ['name' => 'Moda Elite', 'slug' => $slug, 'admin_email' => "admin@{$slug}.com", 'products_count' => 0, 'categories_count' => 0, 'brands_count' => 0];
        }

        $tenant = app(TenantProvisioningService::class)->provision([
            'name' => 'Moda Elite',
            'slug' => $slug,
            'theme_color' => 'pink',
            'plan_id' => $plan->id,
            'admin_name' => 'Maria Garcia',
            'admin_email' => "admin@{$slug}.com",
            'admin_password' => 'password',
            'is_demo' => true,
        ]);

        app()->instance('current_tenant', $tenant);

        $cats = $this->createCategories($tenant->id, [
            ['name' => 'Vestidos', 'slug' => 'vestidos', 'prefix' => 'VES'],
            ['name' => 'Camisas', 'slug' => 'camisas', 'prefix' => 'CAM'],
            ['name' => 'Zapatos', 'slug' => 'zapatos', 'prefix' => 'ZAP'],
            ['name' => 'Bolsos', 'slug' => 'bolsos', 'prefix' => 'BOL'],
        ]);

        $brands = $this->createBrands($tenant->id, ['Zara', 'H&M', 'Nike', 'Adidas']);

        $products = [
            ['name' => 'Vestido Cocktail Negro', 'price' => 189.00, 'cat' => 0, 'brand' => 0, 'qty' => 30],
            ['name' => 'Vestido Floral Verano', 'price' => 129.00, 'cat' => 0, 'brand' => 1, 'qty' => 45],
            ['name' => 'Camisa Oxford Blanca', 'price' => 79.00, 'cat' => 1, 'brand' => 0, 'qty' => 60],
            ['name' => 'Camisa Lino Premium', 'price' => 99.00, 'cat' => 1, 'brand' => 1, 'qty' => 40],
            ['name' => 'Nike Air Max 90', 'price' => 159.00, 'cat' => 2, 'brand' => 2, 'qty' => 50],
            ['name' => 'Adidas Ultraboost 23', 'price' => 189.00, 'cat' => 2, 'brand' => 3, 'qty' => 35],
            ['name' => 'Bolso Tote Cuero', 'price' => 249.00, 'cat' => 3, 'brand' => 0, 'qty' => 20],
            ['name' => 'Mochila Urbana Premium', 'price' => 119.00, 'cat' => 3, 'brand' => 2, 'qty' => 55],
        ];

        $this->createProducts($tenant->id, $cats, $brands, $products);
        $this->createPaymentMethods($tenant->id);
        $this->createCoupons($tenant->id, [
            ['code' => 'MODA20', 'name' => '20% en toda la tienda', 'type' => 'percentage', 'value' => 20],
            ['code' => 'ENVIOGRATIS', 'name' => 'Envio gratis', 'type' => 'free_shipping', 'value' => 0],
        ]);
        $this->createBuyers($tenant->id, $slug, 3);

        return ['name' => 'Moda Elite', 'slug' => $slug, 'admin_email' => "admin@{$slug}.com",
            'products_count' => count($products), 'categories_count' => count($cats), 'brands_count' => count($brands)];
    }

    private function createSportsStore(Plan $plan): array
    {
        $slug = 'deportes-pro';

        if (Tenant::where('slug', $slug)->exists()) {
            $this->command->warn("Tenant {$slug} ya existe, saltando...");

            return ['name' => 'Deportes Pro', 'slug' => $slug, 'admin_email' => "admin@{$slug}.com", 'products_count' => 0, 'categories_count' => 0, 'brands_count' => 0];
        }

        $tenant = app(TenantProvisioningService::class)->provision([
            'name' => 'Deportes Pro',
            'slug' => $slug,
            'theme_color' => 'green',
            'plan_id' => $plan->id,
            'admin_name' => 'Pedro Alvarado',
            'admin_email' => "admin@{$slug}.com",
            'admin_password' => 'password',
            'is_demo' => true,
        ]);

        app()->instance('current_tenant', $tenant);

        $cats = $this->createCategories($tenant->id, [
            ['name' => 'Futbol', 'slug' => 'futbol', 'prefix' => 'FUT'],
            ['name' => 'Running', 'slug' => 'running', 'prefix' => 'RUN'],
            ['name' => 'Gym', 'slug' => 'gym', 'prefix' => 'GYM'],
        ]);

        $brands = $this->createBrands($tenant->id, ['Nike', 'Adidas', 'Under Armour', 'Puma']);

        $products = [
            ['name' => 'Balon Nike Premier League', 'price' => 45.00, 'cat' => 0, 'brand' => 0, 'qty' => 100],
            ['name' => 'Guantes Adidas Predator', 'price' => 89.00, 'cat' => 0, 'brand' => 1, 'qty' => 40],
            ['name' => 'Zapatillas Nike Pegasus 41', 'price' => 139.00, 'cat' => 1, 'brand' => 0, 'qty' => 60],
            ['name' => 'Zapatillas Adidas Supernova', 'price' => 129.00, 'cat' => 1, 'brand' => 1, 'qty' => 45],
            ['name' => 'Pesas Ajustables 20kg', 'price' => 199.00, 'cat' => 2, 'brand' => 2, 'qty' => 25],
            ['name' => 'Banda de Resistencia Set', 'price' => 35.00, 'cat' => 2, 'brand' => 3, 'qty' => 150],
        ];

        $this->createProducts($tenant->id, $cats, $brands, $products);
        $this->createPaymentMethods($tenant->id);
        $this->createCoupons($tenant->id, [
            ['code' => 'SPORT15', 'name' => '15% en deportes', 'type' => 'percentage', 'value' => 15],
        ]);
        $this->createBuyers($tenant->id, $slug, 3);

        return ['name' => 'Deportes Pro', 'slug' => $slug, 'admin_email' => "admin@{$slug}.com",
            'products_count' => count($products), 'categories_count' => count($cats), 'brands_count' => count($brands)];
    }

    private function createHomeStore(Plan $plan): array
    {
        $slug = 'hogar-deco';

        if (Tenant::where('slug', $slug)->exists()) {
            $this->command->warn("Tenant {$slug} ya existe, saltando...");

            return ['name' => 'Hogar & Deco', 'slug' => $slug, 'admin_email' => "admin@{$slug}.com", 'products_count' => 0, 'categories_count' => 0, 'brands_count' => 0];
        }

        $tenant = app(TenantProvisioningService::class)->provision([
            'name' => 'Hogar & Deco',
            'slug' => $slug,
            'theme_color' => 'orange',
            'plan_id' => $plan->id,
            'admin_name' => 'Ana Torres',
            'admin_email' => "admin@{$slug}.com",
            'admin_password' => 'password',
            'is_demo' => true,
        ]);

        app()->instance('current_tenant', $tenant);

        $cats = $this->createCategories($tenant->id, [
            ['name' => 'Sala', 'slug' => 'sala', 'prefix' => 'SAL'],
            ['name' => 'Cocina', 'slug' => 'cocina', 'prefix' => 'COC'],
            ['name' => 'Dormitorio', 'slug' => 'dormitorio', 'prefix' => 'DOR'],
        ]);

        $brands = $this->createBrands($tenant->id, ['IKEA', 'Tramontina', 'Oster']);

        $products = [
            ['name' => 'Sofa Modular 3 Piezas', 'price' => 899.00, 'cat' => 0, 'brand' => 0, 'qty' => 8],
            ['name' => 'Mesa de Centro Roble', 'price' => 349.00, 'cat' => 0, 'brand' => 0, 'qty' => 15],
            ['name' => 'Juego de Ollas 10 Piezas', 'price' => 189.00, 'cat' => 1, 'brand' => 1, 'qty' => 40],
            ['name' => 'Licuadora Oster Pro', 'price' => 129.00, 'cat' => 1, 'brand' => 2, 'qty' => 50],
            ['name' => 'Juego de Sabanas King', 'price' => 79.00, 'cat' => 2, 'brand' => 0, 'qty' => 70],
            ['name' => 'Lampara de Mesa LED', 'price' => 59.00, 'cat' => 2, 'brand' => 0, 'qty' => 90],
        ];

        $this->createProducts($tenant->id, $cats, $brands, $products);
        $this->createPaymentMethods($tenant->id);
        $this->createCoupons($tenant->id, [
            ['code' => 'HOGAR30', 'name' => '$30 descuento', 'type' => 'fixed', 'value' => 30],
        ]);
        $this->createBuyers($tenant->id, $slug, 3);

        return ['name' => 'Hogar & Deco', 'slug' => $slug, 'admin_email' => "admin@{$slug}.com",
            'products_count' => count($products), 'categories_count' => count($cats), 'brands_count' => count($brands)];
    }

    private function createGourmetStore(Plan $plan): array
    {
        $slug = 'gourmet-ec';

        if (Tenant::where('slug', $slug)->exists()) {
            $this->command->warn("Tenant {$slug} ya existe, saltando...");

            return ['name' => 'Gourmet EC', 'slug' => $slug, 'admin_email' => "admin@{$slug}.com", 'products_count' => 0, 'categories_count' => 0, 'brands_count' => 0];
        }

        $tenant = app(TenantProvisioningService::class)->provision([
            'name' => 'Gourmet Ecuador',
            'slug' => $slug,
            'theme_color' => 'red',
            'plan_id' => $plan->id,
            'admin_name' => 'Roberto Chavez',
            'admin_email' => "admin@{$slug}.com",
            'admin_password' => 'password',
            'is_demo' => true,
        ]);

        app()->instance('current_tenant', $tenant);

        $cats = $this->createCategories($tenant->id, [
            ['name' => 'Cafe', 'slug' => 'cafe', 'prefix' => 'CAF'],
            ['name' => 'Chocolate', 'slug' => 'chocolate', 'prefix' => 'CHO'],
            ['name' => 'Especias', 'slug' => 'especias', 'prefix' => 'ESP'],
        ]);

        $brands = $this->createBrands($tenant->id, ['Valdez', 'Pacari', 'Republica del Cacao']);

        $products = [
            ['name' => 'Cafe de Loja Premium 500g', 'price' => 18.00, 'cat' => 0, 'brand' => 0, 'qty' => 200],
            ['name' => 'Cafe Galapagos Organico 250g', 'price' => 25.00, 'cat' => 0, 'brand' => 0, 'qty' => 100],
            ['name' => 'Chocolate Pacari 72% Cacao', 'price' => 6.50, 'cat' => 1, 'brand' => 1, 'qty' => 300],
            ['name' => 'Chocolate Raw 85% Esmeraldas', 'price' => 8.00, 'cat' => 1, 'brand' => 2, 'qty' => 150],
            ['name' => 'Set Especias Amazonia', 'price' => 35.00, 'cat' => 2, 'brand' => 0, 'qty' => 80],
            ['name' => 'Pimienta Rosa Ecuatoriana', 'price' => 12.00, 'cat' => 2, 'brand' => 0, 'qty' => 120],
        ];

        $this->createProducts($tenant->id, $cats, $brands, $products);
        $this->createPaymentMethods($tenant->id);
        $this->createCoupons($tenant->id, [
            ['code' => 'SABOR10', 'name' => '10% gourmet', 'type' => 'percentage', 'value' => 10],
            ['code' => 'PRIMERACOMPRA', 'name' => '$5 primera compra', 'type' => 'fixed', 'value' => 5, 'first_order_only' => true],
        ]);
        $this->createBuyers($tenant->id, $slug, 3);

        return ['name' => 'Gourmet Ecuador', 'slug' => $slug, 'admin_email' => "admin@{$slug}.com",
            'products_count' => count($products), 'categories_count' => count($cats), 'brands_count' => count($brands)];
    }

    // ─── Helper Methods ─────────────────────────────────────────────────

    private function createCategories(int $tenantId, array $categories): array
    {
        $created = [];
        foreach ($categories as $i => $cat) {
            $created[] = Category::create([
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'tenant_id' => $tenantId,
                'is_active' => true,
                'is_featured' => true,
                'position' => $i,
            ]);
        }

        return $created;
    }

    private function createBrands(int $tenantId, array $names): array
    {
        $created = [];
        foreach ($names as $i => $name) {
            $created[] = Brand::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name).'-'.$tenantId,
                'tenant_id' => $tenantId,
                'is_active' => true,
                'is_featured' => true,
                'position' => $i,
            ]);
        }

        return $created;
    }

    private function createProducts(int $tenantId, array $cats, array $brands, array $products): void
    {
        foreach ($products as $p) {
            Product::create([
                'name' => $p['name'],
                'slug' => \Illuminate\Support\Str::slug($p['name']).'-'.$tenantId,
                'sku' => strtoupper(substr(str_replace(' ', '', $p['name']), 0, 4)).'-'.$tenantId.'-'.uniqid(),
                'price' => $p['price'],
                'quantity' => $p['qty'],
                'low_stock_threshold' => max(5, (int) ($p['qty'] * 0.1)),
                'is_active' => true,
                'is_featured' => rand(0, 1) === 1,
                'is_new' => rand(0, 1) === 1,
                'tenant_id' => $tenantId,
                'category_id' => $cats[$p['cat']]->id,
                'brand_id' => $brands[$p['brand']]->id,
                'description' => "<p>Producto de alta calidad: {$p['name']}. Disponible para envio inmediato.</p>",
                'short_description' => "El mejor {$p['name']} al mejor precio.",
            ]);
        }
    }

    private function createPaymentMethods(int $tenantId): void
    {
        $settings = GeneralSetting::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->first();

        if ($settings) {
            $settings->update([
                'payment_gateways_config' => array_merge(
                    $settings->payment_gateways_config ?? [],
                    [
                        'bank_transfer_enabled' => true,
                        'bank_transfer_instructions' => 'Banco Pichincha - Cuenta Ahorros: 2200123456 - Nombre: Empresa S.A.',
                        'bank_transfer_surcharge_percentage' => 0,
                        'bank_transfer_requires_proof' => true,
                        'nuvei_enabled' => true,
                        'nuvei_surcharge_percentage' => 3.50,
                        'cash_on_delivery_enabled' => true,
                        'cash_on_delivery_instructions' => 'Pague al recibir su pedido. Solo efectivo.',
                        'cash_on_delivery_surcharge_percentage' => 1.00,
                        'cash_on_delivery_requires_proof' => false,
                    ]
                ),
            ]);
        }
    }

    private function createCoupons(int $tenantId, array $coupons): void
    {
        foreach ($coupons as $c) {
            Coupon::create([
                'code' => $c['code'],
                'name' => $c['name'],
                'type' => $c['type'],
                'value' => $c['value'],
                'is_active' => true,
                'tenant_id' => $tenantId,
                'usage_count' => 0,
                'first_order_only' => $c['first_order_only'] ?? false,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(3),
                'usage_limit' => 500,
            ]);
        }
    }

    private function createBuyers(int $tenantId, string $slug, int $count): void
    {
        $role = Role::firstOrCreate(['name' => 'customer']);

        for ($i = 1; $i <= $count; $i++) {
            $user = User::create([
                'name' => "Comprador {$i} {$slug}",
                'email' => "buyer{$i}@{$slug}.com",
                'password' => bcrypt('password'),
                'tenant_id' => $tenantId,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->assignRole($role);
        }
    }
}
