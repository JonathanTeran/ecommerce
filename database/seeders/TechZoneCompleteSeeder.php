<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod as PaymentMethodEnum;
use App\Enums\PaymentStatus;
use App\Enums\QuotationStatus;
use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Enums\SectionType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Address;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\EmailCampaign;
use App\Models\GeneralSetting;
use App\Models\HomepageSection;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Page;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\GeneralSetting as PaymentConfig;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductReturn;
use App\Models\ProductVariant;
use App\Models\Quotation;
use App\Models\Review;
use App\Models\StockAlert;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\WarehouseLocation;
use App\Models\Wishlist;
use App\Services\TenantProvisioningService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TechZoneCompleteSeeder extends Seeder
{
    private Tenant $tenant;

    private int $tid;

    private array $categories = [];

    private array $brands = [];

    private array $products = [];

    private array $customers = [];

    private array $warehouses = [];

    private User $admin;

    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'customer']);

        $this->createTenant();
        $this->createGeneralSettings();
        $this->createWarehouseLocations();
        $this->createCategories();
        $this->createBrands();
        $this->createProducts();
        $this->createProductVariants();
        $this->createPaymentMethods();
        $this->createShippingCarriers();
        $this->createCoupons();
        $this->createCustomers();
        $this->createAddresses();
        $this->createOrders();
        $this->createQuotations();
        $this->createReviews();
        $this->createWishlists();
        $this->createCarts();
        $this->createInventoryMovements();
        $this->createStockAlerts();
        $this->createStockTransfers();
        $this->createProductReturns();
        $this->createSupportTickets();
        $this->createBanners();
        $this->createHomepageSections();
        $this->createPages();
        $this->createProductBundles();
        $this->createEmailCampaigns();

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('  TechZone Ecuador - Datos Completos');
        $this->command->info('========================================');
        $this->command->info('  Admin: admin@tech-zone.com / password');
        $this->command->info('  Clientes: 15 registrados');
        $this->command->info('  Productos: ' . count($this->products));
        $this->command->info('  Pedidos: 35 con historial completo');
        $this->command->info('  Cotizaciones: 8 en varios estados');
        $this->command->info('  Reviews: 40+');
        $this->command->info('  Tickets de soporte: 10');
        $this->command->info('  Devoluciones: 5');
        $this->command->info('  Paginas CMS: 3 (quienes-somos, nuestros-servicios, preguntas-frecuentes)');
        $this->command->info('========================================');
    }

    private function createTenant(): void
    {
        $slug = 'tech-zone';

        if (Tenant::where('slug', $slug)->exists()) {
            $this->tenant = Tenant::where('slug', $slug)->first();
            $this->tid = $this->tenant->id;
            $this->admin = User::where('tenant_id', $this->tid)->whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
            app()->instance('current_tenant', $this->tenant);
            $this->command->warn("Tenant tech-zone ya existe (id: {$this->tid}), reutilizando...");

            return;
        }

        $plan = Plan::where('type', 'enterprise')->first();

        $this->tenant = app(TenantProvisioningService::class)->provision([
            'name' => 'TechZone Ecuador',
            'slug' => $slug,
            'domain' => null,
            'theme_color' => 'blue',
            'plan_id' => $plan->id,
            'admin_name' => 'Carlos Mendoza',
            'admin_email' => 'admin@tech-zone.com',
            'admin_password' => 'password',
            'is_demo' => true,
        ]);

        $this->tid = $this->tenant->id;
        $this->admin = User::where('email', 'admin@tech-zone.com')->first();
        app()->instance('current_tenant', $this->tenant);

        Tenant::where('is_default', true)->where('id', '!=', $this->tid)->update(['is_default' => false]);
        $this->tenant->update(['is_default' => true]);
    }

    private function createGeneralSettings(): void
    {
        $settings = GeneralSetting::where('tenant_id', $this->tid)->first();
        if (! $settings) {
            return;
        }

        $settings->update([
            'site_name' => 'TechZone Ecuador',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'tax_rate' => 15.00,
            'quotation_validity_days' => 15,
            'quotation_prefix' => 'TZ-COT',
            'cart_expiration_days' => 7,
            'abandoned_cart_reminder_hours' => 24,
            'low_stock_threshold' => 5,
            'navbar_config' => [
                'show_search' => true,
                'show_categories_button' => true,
                'style' => 'transparent_on_scroll',
                'menu_items' => [
                    ['label' => 'Inicio', 'url' => '/', 'open_in_new_tab' => false, 'is_visible' => true],
                    ['label' => 'Tienda', 'url' => '/shop', 'open_in_new_tab' => false, 'is_visible' => true],
                    ['label' => 'Marcas', 'url' => '/brands', 'open_in_new_tab' => false, 'is_visible' => true],
                    ['label' => 'Servicios', 'url' => '/pagina/nuestros-servicios', 'open_in_new_tab' => false, 'is_visible' => true],
                    ['label' => 'Quienes Somos', 'url' => '/pagina/quienes-somos', 'open_in_new_tab' => false, 'is_visible' => true],
                    ['label' => 'FAQ', 'url' => '/pagina/preguntas-frecuentes', 'open_in_new_tab' => false, 'is_visible' => true],
                ],
            ],
            'social_links' => [
                'facebook' => 'https://facebook.com/techzoneec',
                'instagram' => 'https://instagram.com/techzoneec',
                'twitter' => 'https://x.com/techzoneec',
                'tiktok' => 'https://tiktok.com/@techzoneec',
                'whatsapp' => '+593987654321',
            ],
            'brands_page_config' => [
                'is_enabled' => true,
                'title' => 'Nuestras Marcas',
                'subtitle' => 'Trabajamos con las marcas lideres en tecnologia a nivel mundial para ofrecerte productos de la mas alta calidad.',
                'columns' => 6,
                'show_product_count' => true,
                'meta_title' => 'Marcas | TechZone Ecuador',
                'meta_description' => 'Descubre las mejores marcas de tecnologia: Apple, Samsung, Dell, HP, Lenovo, ASUS y muchas mas en TechZone Ecuador.',
            ],
            'store_policies' => [
                'return_policy' => 'Aceptamos devoluciones dentro de los 15 dias posteriores a la compra. El producto debe estar en su empaque original y sin uso.',
                'shipping_policy' => 'Envios a todo Ecuador. Entrega en Quito y Guayaquil: 1-2 dias. Resto del pais: 3-5 dias habiles.',
                'privacy_policy' => 'Protegemos sus datos personales de acuerdo con la Ley Organica de Proteccion de Datos Personales del Ecuador.',
                'terms_conditions' => 'Al realizar una compra en TechZone Ecuador, acepta nuestros terminos y condiciones de venta.',
            ],
        ]);
    }

    private function createWarehouseLocations(): void
    {
        $locations = [
            ['name' => 'Bodega Principal Quito', 'code' => 'TZ-UIO-01', 'address' => 'Av. Amazonas N35-17 y Juan Pablo Sanz, Quito', 'is_default' => true],
            ['name' => 'Punto de Venta Guayaquil', 'code' => 'TZ-GYE-01', 'address' => 'Mall del Sol, Local 215, Guayaquil', 'is_default' => false],
            ['name' => 'Centro de Distribucion Cuenca', 'code' => 'TZ-CUE-01', 'address' => 'Parque Industrial, Cuenca', 'is_default' => false],
        ];

        foreach ($locations as $loc) {
            $this->warehouses[] = WarehouseLocation::withoutGlobalScopes()->firstOrCreate(
                ['code' => $loc['code'], 'tenant_id' => $this->tid],
                array_merge($loc, ['is_active' => true, 'tenant_id' => $this->tid])
            );
        }
    }

    private function createCategories(): void
    {
        $tree = [
            'Laptops' => ['Gaming', 'Empresariales', 'Ultrabooks', 'Chromebooks'],
            'Smartphones' => ['Gama Alta', 'Gama Media', 'Accesorios Moviles'],
            'Componentes PC' => ['Procesadores', 'Tarjetas de Video', 'Memorias RAM', 'Almacenamiento SSD', 'Fuentes de Poder', 'Cases'],
            'Perifericos' => ['Teclados', 'Ratones', 'Monitores', 'Audifonos', 'Webcams'],
            'Redes y Conectividad' => ['Routers', 'Switches', 'Access Points', 'Cables y Adaptadores'],
            'Impresoras' => ['Laser', 'Inkjet', 'Multifuncionales', 'Consumibles'],
            'Gaming' => ['Consolas', 'Accesorios Gaming', 'Sillas Gaming', 'Streaming'],
            'Software y Licencias' => ['Sistemas Operativos', 'Offimatica', 'Antivirus', 'Diseno'],
        ];

        $position = 0;
        foreach ($tree as $parentName => $children) {
            $parent = Category::withoutGlobalScopes()->firstOrCreate(
                ['slug' => Str::slug($parentName) . '-tz', 'tenant_id' => $this->tid],
                ['name' => $parentName, 'is_active' => true, 'is_featured' => true, 'position' => $position++, 'tenant_id' => $this->tid]
            );
            $this->categories[$parentName] = $parent;

            foreach ($children as $i => $childName) {
                $child = Category::withoutGlobalScopes()->firstOrCreate(
                    ['slug' => Str::slug($childName) . '-tz', 'tenant_id' => $this->tid],
                    ['name' => $childName, 'parent_id' => $parent->id, 'is_active' => true, 'position' => $i, 'tenant_id' => $this->tid]
                );
                $this->categories[$childName] = $child;
            }
        }
    }

    private function createBrands(): void
    {
        $brandList = [
            'Apple', 'Samsung', 'Dell', 'HP', 'Lenovo', 'ASUS', 'Acer',
            'Logitech', 'Razer', 'Corsair', 'NVIDIA', 'AMD', 'Intel',
            'Kingston', 'Western Digital', 'TP-Link', 'Epson', 'Canon',
            'Microsoft', 'Sony', 'HyperX', 'MSI',
        ];

        foreach ($brandList as $i => $name) {
            $this->brands[$name] = Brand::withoutGlobalScopes()->firstOrCreate(
                ['slug' => Str::slug($name) . '-tz', 'tenant_id' => $this->tid],
                ['name' => $name, 'is_active' => true, 'is_featured' => $i < 10, 'position' => $i, 'tenant_id' => $this->tid]
            );
        }
    }

    private function createProducts(): void
    {
        $items = [
            ['name' => 'MacBook Pro 16" M3 Max', 'sku' => 'TZ-LAP-001', 'price' => 3499.00, 'compare_price' => 3799.00, 'cost' => 2800.00, 'qty' => 12, 'cat' => 'Laptops', 'brand' => 'Apple', 'desc' => 'Chip M3 Max, 36GB RAM, 1TB SSD, pantalla Liquid Retina XDR.', 'specs' => ['Procesador' => 'Apple M3 Max', 'RAM' => '36GB Unificada', 'Almacenamiento' => '1TB SSD', 'Pantalla' => '16.2" Liquid Retina XDR', 'Bateria' => '22 horas']],
            ['name' => 'Dell XPS 15 9530', 'sku' => 'TZ-LAP-002', 'price' => 1899.00, 'compare_price' => 2099.00, 'cost' => 1500.00, 'qty' => 18, 'cat' => 'Ultrabooks', 'brand' => 'Dell', 'desc' => 'Intel Core i7-13700H, 16GB DDR5, 512GB SSD NVMe, OLED 3.5K.', 'specs' => ['Procesador' => 'Intel i7-13700H', 'RAM' => '16GB DDR5', 'Almacenamiento' => '512GB NVMe', 'Pantalla' => '15.6" OLED 3.5K']],
            ['name' => 'ASUS ROG Strix G16', 'sku' => 'TZ-LAP-003', 'price' => 2199.00, 'compare_price' => null, 'cost' => 1750.00, 'qty' => 10, 'cat' => 'Gaming', 'brand' => 'ASUS', 'desc' => 'Intel i9-13980HX, RTX 4070, 16GB DDR5, 1TB SSD, 165Hz QHD.', 'specs' => ['Procesador' => 'Intel i9-13980HX', 'GPU' => 'NVIDIA RTX 4070 8GB', 'RAM' => '16GB DDR5', 'Pantalla' => '16" QHD 165Hz']],
            ['name' => 'Lenovo ThinkPad X1 Carbon Gen 11', 'sku' => 'TZ-LAP-004', 'price' => 1749.00, 'compare_price' => 1899.00, 'cost' => 1350.00, 'qty' => 15, 'cat' => 'Empresariales', 'brand' => 'Lenovo', 'desc' => 'Intel i7-1365U vPro, 16GB, 512GB SSD, 14" 2.8K OLED.', 'specs' => ['Procesador' => 'Intel i7-1365U vPro', 'RAM' => '16GB LPDDR5', 'Pantalla' => '14" 2.8K OLED', 'Peso' => '1.12 kg']],
            ['name' => 'HP Pavilion 15', 'sku' => 'TZ-LAP-005', 'price' => 699.00, 'compare_price' => 799.00, 'cost' => 520.00, 'qty' => 30, 'cat' => 'Laptops', 'brand' => 'HP', 'desc' => 'AMD Ryzen 5 7530U, 8GB RAM, 256GB SSD, FHD IPS 15.6".', 'specs' => ['Procesador' => 'AMD Ryzen 5 7530U', 'RAM' => '8GB DDR4', 'Almacenamiento' => '256GB SSD']],
            ['name' => 'Acer Chromebook 314', 'sku' => 'TZ-LAP-006', 'price' => 299.00, 'compare_price' => null, 'cost' => 200.00, 'qty' => 25, 'cat' => 'Chromebooks', 'brand' => 'Acer', 'desc' => 'MediaTek MT8183, 4GB RAM, 64GB eMMC, Chrome OS.', 'specs' => ['Procesador' => 'MediaTek MT8183', 'RAM' => '4GB', 'SO' => 'Chrome OS', 'Bateria' => '12 horas']],
            ['name' => 'iPhone 16 Pro Max 256GB', 'sku' => 'TZ-PHN-001', 'price' => 1499.00, 'compare_price' => null, 'cost' => 1200.00, 'qty' => 40, 'cat' => 'Gama Alta', 'brand' => 'Apple', 'desc' => 'Chip A18 Pro, camara 48MP zoom 5x, 6.9" Super Retina XDR, titanio.', 'specs' => ['Chip' => 'A18 Pro', 'Pantalla' => '6.9" Super Retina XDR', 'Camara' => '48MP + 12MP + 12MP']],
            ['name' => 'Samsung Galaxy S24 Ultra', 'sku' => 'TZ-PHN-002', 'price' => 1349.00, 'compare_price' => 1449.00, 'cost' => 1050.00, 'qty' => 35, 'cat' => 'Gama Alta', 'brand' => 'Samsung', 'desc' => 'Snapdragon 8 Gen 3, 12GB RAM, 256GB, camara 200MP, S Pen.', 'specs' => ['Procesador' => 'Snapdragon 8 Gen 3', 'RAM' => '12GB', 'Camara' => '200MP + 50MP + 12MP + 10MP']],
            ['name' => 'Samsung Galaxy A55 5G', 'sku' => 'TZ-PHN-003', 'price' => 449.00, 'compare_price' => 499.00, 'cost' => 320.00, 'qty' => 50, 'cat' => 'Gama Media', 'brand' => 'Samsung', 'desc' => 'Exynos 1480, 8GB RAM, 128GB, Super AMOLED 120Hz.', 'specs' => ['Procesador' => 'Exynos 1480', 'Pantalla' => '6.6" Super AMOLED 120Hz', 'Bateria' => '5000mAh']],
            ['name' => 'NVIDIA GeForce RTX 4090 24GB', 'sku' => 'TZ-GPU-001', 'price' => 1999.00, 'compare_price' => null, 'cost' => 1600.00, 'qty' => 5, 'cat' => 'Tarjetas de Video', 'brand' => 'NVIDIA', 'desc' => 'Ada Lovelace, 24GB GDDR6X, DLSS 3, ray tracing 3ra gen.', 'specs' => ['VRAM' => '24GB GDDR6X', 'CUDA Cores' => '16384', 'TDP' => '450W']],
            ['name' => 'AMD Ryzen 9 7950X', 'sku' => 'TZ-CPU-001', 'price' => 549.00, 'compare_price' => 699.00, 'cost' => 420.00, 'qty' => 20, 'cat' => 'Procesadores', 'brand' => 'AMD', 'desc' => '16 nucleos, 32 hilos, hasta 5.7GHz, 80MB cache, socket AM5.', 'specs' => ['Nucleos' => '16C / 32T', 'Frecuencia' => '4.5 - 5.7 GHz', 'Socket' => 'AM5']],
            ['name' => 'Intel Core i7-14700K', 'sku' => 'TZ-CPU-002', 'price' => 399.00, 'compare_price' => 449.00, 'cost' => 310.00, 'qty' => 22, 'cat' => 'Procesadores', 'brand' => 'Intel', 'desc' => '20 nucleos (8P+12E), hasta 5.6GHz, LGA 1700.', 'specs' => ['Nucleos' => '20C / 28T', 'Frecuencia' => '3.4 - 5.6 GHz', 'Socket' => 'LGA 1700']],
            ['name' => 'Corsair Vengeance DDR5 32GB', 'sku' => 'TZ-RAM-001', 'price' => 129.00, 'compare_price' => 159.00, 'cost' => 85.00, 'qty' => 45, 'cat' => 'Memorias RAM', 'brand' => 'Corsair', 'desc' => 'DDR5-5600MHz, CL36, Intel XMP 3.0, disipador aluminio.', 'specs' => ['Tipo' => 'DDR5', 'Velocidad' => '5600 MHz', 'Latencia' => 'CL36']],
            ['name' => 'Samsung 990 Pro 2TB NVMe', 'sku' => 'TZ-SSD-001', 'price' => 189.00, 'compare_price' => 229.00, 'cost' => 130.00, 'qty' => 35, 'cat' => 'Almacenamiento SSD', 'brand' => 'Samsung', 'desc' => 'PCIe 4.0 x4, lectura 7450MB/s, escritura 6900MB/s.', 'specs' => ['Capacidad' => '2TB', 'Lectura' => '7450 MB/s', 'Escritura' => '6900 MB/s']],
            ['name' => 'Kingston FURY Beast 16GB DDR4', 'sku' => 'TZ-RAM-002', 'price' => 49.00, 'compare_price' => null, 'cost' => 30.00, 'qty' => 60, 'cat' => 'Memorias RAM', 'brand' => 'Kingston', 'desc' => 'DDR4-3200MHz, CL16, Plug N Play.', 'specs' => ['Tipo' => 'DDR4', 'Velocidad' => '3200 MHz', 'Capacidad' => '16GB']],
            ['name' => 'Corsair RM850x 850W 80+ Gold', 'sku' => 'TZ-PSU-001', 'price' => 139.00, 'compare_price' => null, 'cost' => 95.00, 'qty' => 25, 'cat' => 'Fuentes de Poder', 'brand' => 'Corsair', 'desc' => 'Full modular, 80+ Gold, ventilador 135mm Zero RPM.', 'specs' => ['Potencia' => '850W', 'Certificacion' => '80 PLUS Gold', 'Modular' => 'Full Modular']],
            ['name' => 'Logitech MX Master 3S', 'sku' => 'TZ-PER-001', 'price' => 99.00, 'compare_price' => null, 'cost' => 65.00, 'qty' => 70, 'cat' => 'Ratones', 'brand' => 'Logitech', 'desc' => 'Sensor 8000 DPI, scroll MagSpeed, USB-C, Bluetooth.', 'specs' => ['Sensor' => '8000 DPI', 'Bateria' => '70 dias']],
            ['name' => 'Razer DeathAdder V3 Pro', 'sku' => 'TZ-PER-002', 'price' => 149.00, 'compare_price' => 169.00, 'cost' => 100.00, 'qty' => 40, 'cat' => 'Ratones', 'brand' => 'Razer', 'desc' => 'Sensor Focus Pro 30K, 63g, HyperSpeed wireless.', 'specs' => ['Sensor' => 'Focus Pro 30K', 'Peso' => '63g', 'Polling Rate' => '4000Hz']],
            ['name' => 'Logitech MX Keys S', 'sku' => 'TZ-PER-003', 'price' => 109.00, 'compare_price' => null, 'cost' => 72.00, 'qty' => 55, 'cat' => 'Teclados', 'brand' => 'Logitech', 'desc' => 'Retroiluminacion inteligente, teclas esfericas, multi-dispositivo.', 'specs' => ['Tipo' => 'Membrana Low Profile', 'Conectividad' => 'Bluetooth + Logi Bolt']],
            ['name' => 'Corsair K70 RGB PRO', 'sku' => 'TZ-PER-004', 'price' => 159.00, 'compare_price' => null, 'cost' => 110.00, 'qty' => 30, 'cat' => 'Teclados', 'brand' => 'Corsair', 'desc' => 'Cherry MX Red, marco aluminio, PBT keycaps, polling 8000Hz.', 'specs' => ['Switches' => 'Cherry MX Red', 'Keycaps' => 'PBT Double-Shot']],
            ['name' => 'Dell UltraSharp U2723QE 27" 4K', 'sku' => 'TZ-MON-001', 'price' => 549.00, 'compare_price' => 629.00, 'cost' => 400.00, 'qty' => 15, 'cat' => 'Monitores', 'brand' => 'Dell', 'desc' => 'IPS Black, USB-C 90W, 4K, HDR400, 98% DCI-P3.', 'specs' => ['Resolucion' => '3840x2160', 'Panel' => 'IPS Black', 'Color' => '98% DCI-P3']],
            ['name' => 'Samsung Odyssey G7 32" 240Hz', 'sku' => 'TZ-MON-002', 'price' => 649.00, 'compare_price' => 749.00, 'cost' => 480.00, 'qty' => 12, 'cat' => 'Monitores', 'brand' => 'Samsung', 'desc' => 'VA curvo 1000R, 1ms, 240Hz, HDR600, G-Sync + FreeSync.', 'specs' => ['Resolucion' => '2560x1440 QHD', 'Refresh' => '240Hz / 1ms', 'HDR' => 'HDR600']],
            ['name' => 'HyperX Cloud III Wireless', 'sku' => 'TZ-AUD-001', 'price' => 149.00, 'compare_price' => null, 'cost' => 100.00, 'qty' => 45, 'cat' => 'Audifonos', 'brand' => 'HyperX', 'desc' => 'DTS Spatial Audio, 53mm drivers, 120h bateria.', 'specs' => ['Driver' => '53mm', 'Bateria' => '120 horas', 'Audio' => 'DTS Spatial']],
            ['name' => 'Logitech C920s HD Pro Webcam', 'sku' => 'TZ-WEB-001', 'price' => 69.00, 'compare_price' => 79.00, 'cost' => 42.00, 'qty' => 35, 'cat' => 'Webcams', 'brand' => 'Logitech', 'desc' => '1080p 30fps, autoenfoque HD, doble mic estereo.', 'specs' => ['Resolucion' => '1080p 30fps', 'FOV' => '78 grados']],
            ['name' => 'TP-Link Archer AX73 WiFi 6', 'sku' => 'TZ-NET-001', 'price' => 119.00, 'compare_price' => 139.00, 'cost' => 78.00, 'qty' => 40, 'cat' => 'Routers', 'brand' => 'TP-Link', 'desc' => 'AX5400, WiFi 6, 6 antenas, 4 puertos Gigabit, USB 3.0.', 'specs' => ['Estandar' => 'WiFi 6 (802.11ax)', 'Velocidad' => '5400 Mbps']],
            ['name' => 'Epson EcoTank L3250', 'sku' => 'TZ-IMP-001', 'price' => 259.00, 'compare_price' => 299.00, 'cost' => 180.00, 'qty' => 20, 'cat' => 'Multifuncionales', 'brand' => 'Epson', 'desc' => 'Tanque de tinta, WiFi, imprime/copia/escanea.', 'specs' => ['Tipo' => 'Inyeccion (EcoTank)', 'Funciones' => 'Imprime, Copia, Escanea']],
            ['name' => 'Sony PlayStation 5 Slim', 'sku' => 'TZ-GAM-001', 'price' => 499.00, 'compare_price' => null, 'cost' => 400.00, 'qty' => 15, 'cat' => 'Consolas', 'brand' => 'Sony', 'desc' => 'SSD 1TB, lector de discos, DualSense, 4K 120fps.', 'specs' => ['Almacenamiento' => '1TB SSD', 'Resolucion' => '4K 120fps']],
            ['name' => 'MSI MAG CH130 I Silla Gaming', 'sku' => 'TZ-GAM-002', 'price' => 349.00, 'compare_price' => 399.00, 'cost' => 220.00, 'qty' => 10, 'cat' => 'Sillas Gaming', 'brand' => 'MSI', 'desc' => 'Espuma alta densidad, reclinable 180, reposabrazos 4D.', 'specs' => ['Material' => 'PU Leather', 'Reclinable' => '180 grados', 'Peso max' => '150 kg']],
            ['name' => 'Microsoft 365 Personal (1 Ano)', 'sku' => 'TZ-SW-001', 'price' => 69.00, 'compare_price' => null, 'cost' => 50.00, 'qty' => 200, 'cat' => 'Offimatica', 'brand' => 'Microsoft', 'desc' => 'Word, Excel, PowerPoint, Outlook, 1TB OneDrive.', 'specs' => ['Apps' => 'Word, Excel, PowerPoint, Outlook', 'Nube' => '1TB OneDrive']],
            ['name' => 'Windows 11 Pro (Licencia Digital)', 'sku' => 'TZ-SW-002', 'price' => 199.00, 'compare_price' => null, 'cost' => 140.00, 'qty' => 100, 'cat' => 'Sistemas Operativos', 'brand' => 'Microsoft', 'desc' => 'Licencia perpetua, BitLocker, RDP, Hyper-V.', 'specs' => ['Tipo' => 'Licencia perpetua digital', 'Caracteristicas' => 'BitLocker, Hyper-V, RDP']],
            ['name' => 'AirPods Pro 2 USB-C', 'sku' => 'TZ-ACC-001', 'price' => 249.00, 'compare_price' => null, 'cost' => 185.00, 'qty' => 55, 'cat' => 'Accesorios Moviles', 'brand' => 'Apple', 'desc' => 'ANC adaptativa, chip H2, USB-C, IP54.', 'specs' => ['Chip' => 'H2', 'ANC' => 'Adaptativa', 'Bateria' => '6h (30h con estuche)']],
        ];

        foreach ($items as $item) {
            $cat = $this->categories[$item['cat']] ?? null;
            $brand = $this->brands[$item['brand']] ?? null;

            $product = Product::withoutGlobalScopes()->firstOrCreate(
                ['sku' => $item['sku'], 'tenant_id' => $this->tid],
                [
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']) . '-tz',
                    'description' => '<p>' . $item['desc'] . '</p>',
                    'short_description' => Str::limit($item['desc'], 100),
                    'price' => $item['price'],
                    'compare_price' => $item['compare_price'],
                    'cost' => $item['cost'],
                    'quantity' => $item['qty'],
                    'low_stock_threshold' => max(3, (int) ($item['qty'] * 0.15)),
                    'category_id' => $cat?->id,
                    'brand_id' => $brand?->id,
                    'specifications' => $item['specs'],
                    'is_active' => true,
                    'is_featured' => $item['price'] > 500,
                    'is_new' => rand(0, 3) === 0,
                    'requires_shipping' => ! str_starts_with($item['sku'], 'TZ-SW'),
                    'warranty_months' => str_starts_with($item['sku'], 'TZ-SW') ? 0 : 12,
                    'views_count' => rand(50, 2000),
                    'sales_count' => rand(5, 150),
                    'tenant_id' => $this->tid,
                ]
            );

            $this->products[$item['sku']] = $product;
        }
    }

    private function createProductVariants(): void
    {
        $variants = [
            'TZ-PHN-001' => [
                ['name' => '256GB - Titanio Natural', 'sku' => 'TZ-PHN-001-256NAT', 'price' => 1499.00, 'quantity' => 15, 'options' => ['storage' => '256GB', 'color' => 'Titanio Natural']],
                ['name' => '256GB - Titanio Negro', 'sku' => 'TZ-PHN-001-256BLK', 'price' => 1499.00, 'quantity' => 12, 'options' => ['storage' => '256GB', 'color' => 'Titanio Negro']],
                ['name' => '512GB - Titanio Natural', 'sku' => 'TZ-PHN-001-512NAT', 'price' => 1699.00, 'quantity' => 8, 'options' => ['storage' => '512GB', 'color' => 'Titanio Natural']],
                ['name' => '1TB - Titanio Negro', 'sku' => 'TZ-PHN-001-1TBLK', 'price' => 1999.00, 'quantity' => 5, 'options' => ['storage' => '1TB', 'color' => 'Titanio Negro']],
            ],
            'TZ-PHN-002' => [
                ['name' => '256GB - Titanium Gray', 'sku' => 'TZ-PHN-002-256GR', 'price' => 1349.00, 'quantity' => 12, 'options' => ['storage' => '256GB', 'color' => 'Titanium Gray']],
                ['name' => '512GB - Titanium Violet', 'sku' => 'TZ-PHN-002-512VI', 'price' => 1499.00, 'quantity' => 8, 'options' => ['storage' => '512GB', 'color' => 'Titanium Violet']],
            ],
            'TZ-RAM-001' => [
                ['name' => '32GB (2x16GB) 5600MHz', 'sku' => 'TZ-RAM-001-32', 'price' => 129.00, 'quantity' => 25, 'options' => ['capacity' => '32GB', 'speed' => '5600MHz']],
                ['name' => '64GB (2x32GB) 5600MHz', 'sku' => 'TZ-RAM-001-64', 'price' => 239.00, 'quantity' => 10, 'options' => ['capacity' => '64GB', 'speed' => '5600MHz']],
            ],
        ];

        foreach ($variants as $parentSku => $variantList) {
            $product = $this->products[$parentSku] ?? null;
            if (! $product) {
                continue;
            }

            foreach ($variantList as $v) {
                ProductVariant::firstOrCreate(
                    ['sku' => $v['sku'], 'product_id' => $product->id],
                    ['name' => $v['name'], 'price' => $v['price'], 'quantity' => $v['quantity'], 'options' => $v['options'], 'is_active' => true]
                );
            }
        }
    }

    private function createPaymentMethods(): void
    {
        $settings = \App\Models\GeneralSetting::withoutGlobalScopes()
            ->where('tenant_id', $this->tid)->first();

        if ($settings) {
            $settings->update([
                'payment_gateways_config' => array_merge(
                    $settings->payment_gateways_config ?? [],
                    [
                        'bank_transfer_enabled' => true,
                        'bank_transfer_instructions' => 'Banco Pichincha - Cta. Ahorros: 2200456789 - TechZone Ecuador S.A. - RUC: 1792876543001',
                        'bank_transfer_surcharge_percentage' => 0,
                        'bank_transfer_requires_proof' => true,
                        'nuvei_enabled' => true,
                        'nuvei_surcharge_percentage' => 3.57,
                        'payphone_enabled' => true,
                        'payphone_surcharge_percentage' => 2.00,
                        'cash_on_delivery_enabled' => true,
                        'cash_on_delivery_instructions' => 'Pague al recibir. Solo Quito y valles.',
                        'cash_on_delivery_surcharge_percentage' => 1.50,
                        'cash_on_delivery_requires_proof' => false,
                    ]
                ),
            ]);
        }
    }

    private function createShippingCarriers(): void
    {
        $settings = GeneralSetting::withoutGlobalScopes()
            ->where('tenant_id', $this->tid)
            ->first();

        if ($settings) {
            $settings->update([
                'shipping_config' => [
                    'carriers' => [
                        [
                            'key' => 'servientrega',
                            'name' => 'Servientrega',
                            'tracking_url_template' => 'https://www.servientrega.com.ec/rastreo/{tracking_number}',
                            'phone' => '1800-737-843',
                            'is_active' => true,
                            'rates' => [
                                ['name' => 'Quito / Guayaquil', 'price' => 3.50, 'estimated_days' => '2', 'zone' => 'principal', 'is_active' => true],
                                ['name' => 'Resto del Pais', 'price' => 5.99, 'estimated_days' => '5', 'zone' => 'nacional', 'is_active' => true],
                                ['name' => 'Envio Gratis (+$200)', 'price' => 0, 'min_order_amount' => 200.00, 'estimated_days' => '3', 'zone' => 'nacional', 'is_active' => true],
                            ],
                        ],
                        [
                            'key' => 'tramaco',
                            'name' => 'Tramaco Express',
                            'tracking_url_template' => 'https://tramaco.com.ec/rastreo/{tracking_number}',
                            'phone' => '02-2444-500',
                            'is_active' => true,
                            'rates' => [],
                        ],
                        [
                            'key' => 'tz-express',
                            'name' => 'TechZone Express (Quito)',
                            'phone' => '+593987654321',
                            'is_active' => true,
                            'rates' => [
                                ['name' => 'Same Day Quito', 'price' => 4.99, 'estimated_days' => '0', 'zone' => 'quito', 'is_active' => true],
                            ],
                        ],
                    ],
                ],
            ]);
        }
    }

    private function createCoupons(): void
    {
        $coupons = [
            ['code' => 'BIENVENIDO15', 'name' => '15% primera compra', 'type' => 'percentage', 'value' => 15, 'max_discount_amount' => 50, 'first_order_only' => true, 'usage_limit' => 1000],
            ['code' => 'TECH10', 'name' => '10% en tecnologia', 'type' => 'percentage', 'value' => 10, 'max_discount_amount' => 100, 'first_order_only' => false, 'usage_limit' => 500],
            ['code' => 'LAPTOP50', 'name' => '$50 off en laptops', 'type' => 'fixed', 'value' => 50, 'min_order_amount' => 500, 'first_order_only' => false, 'usage_limit' => 200],
            ['code' => 'ENVIOGRATIS', 'name' => 'Envio gratuito', 'type' => 'free_shipping', 'value' => 0, 'min_order_amount' => 100, 'first_order_only' => false, 'usage_limit' => 300],
            ['code' => 'GAMING20', 'name' => '20% perifericos gaming', 'type' => 'percentage', 'value' => 20, 'max_discount_amount' => 80, 'first_order_only' => false, 'usage_limit' => 150],
        ];

        foreach ($coupons as $c) {
            Coupon::withoutGlobalScopes()->firstOrCreate(
                ['code' => $c['code'], 'tenant_id' => $this->tid],
                array_merge($c, ['is_active' => true, 'usage_count' => rand(0, 50), 'starts_at' => now()->subMonth(), 'expires_at' => now()->addMonths(6), 'tenant_id' => $this->tid])
            );
        }
    }

    private function createCustomers(): void
    {
        $people = [
            ['name' => 'Maria Fernanda Lopez', 'email' => 'maria.lopez@gmail.com', 'phone' => '+593998123456', 'id_type' => 'cedula', 'id_number' => '1712345678'],
            ['name' => 'Juan Carlos Ortega', 'email' => 'jc.ortega@hotmail.com', 'phone' => '+593987654321', 'id_type' => 'cedula', 'id_number' => '0912345679'],
            ['name' => 'Andrea Paola Suarez', 'email' => 'andrea.suarez@yahoo.com', 'phone' => '+593976543210', 'id_type' => 'cedula', 'id_number' => '0112345680'],
            ['name' => 'Roberto Andres Villacis', 'email' => 'r.villacis@outlook.com', 'phone' => '+593965432109', 'id_type' => 'ruc', 'id_number' => '1712345681001'],
            ['name' => 'Gabriela Estefania Torres', 'email' => 'gaby.torres@gmail.com', 'phone' => '+593954321098', 'id_type' => 'cedula', 'id_number' => '1312345682'],
            ['name' => 'Luis Miguel Herrera', 'email' => 'luis.herrera@empresa.ec', 'phone' => '+593943210987', 'id_type' => 'ruc', 'id_number' => '1792876543001'],
            ['name' => 'Sofia Alejandra Paz', 'email' => 'sofi.paz@gmail.com', 'phone' => '+593932109876', 'id_type' => 'cedula', 'id_number' => '0612345684'],
            ['name' => 'Daniel Esteban Morales', 'email' => 'daniel.morales@live.com', 'phone' => '+593921098765', 'id_type' => 'cedula', 'id_number' => '1812345685'],
            ['name' => 'Camila Valentina Reyes', 'email' => 'cami.reyes@gmail.com', 'phone' => '+593910987654', 'id_type' => 'cedula', 'id_number' => '1012345686'],
            ['name' => 'Fernando Xavier Ponce', 'email' => 'fer.ponce@protonmail.com', 'phone' => '+593909876543', 'id_type' => 'cedula', 'id_number' => '0712345687'],
            ['name' => 'Valeria Michelle Castro', 'email' => 'vale.castro@gmail.com', 'phone' => '+593898765432', 'id_type' => 'cedula', 'id_number' => '1112345688'],
            ['name' => 'Sebastian Andres Aguirre', 'email' => 'seba.aguirre@outlook.com', 'phone' => '+593887654321', 'id_type' => 'cedula', 'id_number' => '0212345689'],
            ['name' => 'Isabella Salome Flores', 'email' => 'isa.flores@hotmail.com', 'phone' => '+593876543210', 'id_type' => 'cedula', 'id_number' => '1512345690'],
            ['name' => 'Mateo Alejandro Vega', 'email' => 'mateo.vega@gmail.com', 'phone' => '+593865432109', 'id_type' => 'cedula', 'id_number' => '0412345691'],
            ['name' => 'Empresa TechCorp S.A.', 'email' => 'compras@techcorp.ec', 'phone' => '+59322456789', 'id_type' => 'ruc', 'id_number' => '1791234567001'],
        ];

        foreach ($people as $p) {
            $user = User::withoutGlobalScopes()->firstOrCreate(
                ['email' => $p['email'], 'tenant_id' => $this->tid],
                [
                    'name' => $p['name'], 'password' => Hash::make('password'), 'phone' => $p['phone'],
                    'identification_type' => $p['id_type'], 'identification_number' => $p['id_number'],
                    'is_active' => true, 'email_verified_at' => now()->subDays(rand(1, 90)),
                    'last_login_at' => now()->subDays(rand(0, 30)), 'newsletter_subscribed' => rand(0, 1) === 1,
                    'tenant_id' => $this->tid,
                ]
            );
            $user->assignRole('customer');
            $this->customers[] = $user;
        }
    }

    private function createAddresses(): void
    {
        $addrs = [
            ['city' => 'Quito', 'prov' => 'Pichincha', 'addr' => 'Av. Naciones Unidas E5-22 y Amazonas'],
            ['city' => 'Guayaquil', 'prov' => 'Guayas', 'addr' => 'Cdla. Kennedy Norte, Av. Francisco de Orellana'],
            ['city' => 'Cuenca', 'prov' => 'Azuay', 'addr' => 'Calle Larga 7-23 y Borrero'],
            ['city' => 'Quito', 'prov' => 'Pichincha', 'addr' => 'Cumbaya, Av. Interoceania km 12'],
        ];

        foreach ($this->customers as $i => $customer) {
            $a = $addrs[$i % count($addrs)];
            Address::withoutGlobalScopes()->firstOrCreate(
                ['user_id' => $customer->id, 'type' => 'shipping', 'tenant_id' => $this->tid],
                ['label' => 'Casa', 'first_name' => explode(' ', $customer->name)[0], 'last_name' => explode(' ', $customer->name, 2)[1] ?? '', 'address_line_1' => $a['addr'], 'city' => $a['city'], 'province' => $a['prov'], 'country' => 'EC', 'phone' => $customer->phone, 'is_default' => true, 'tenant_id' => $this->tid]
            );
        }
    }

    private function createOrders(): void
    {
        $productArr = array_values($this->products);
        if (empty($productArr) || empty($this->customers)) {
            return;
        }

        $statuses = [
            OrderStatus::DELIVERED, OrderStatus::DELIVERED, OrderStatus::DELIVERED, OrderStatus::DELIVERED,
            OrderStatus::SHIPPED, OrderStatus::SHIPPED, OrderStatus::PROCESSING, OrderStatus::PROCESSING,
            OrderStatus::CONFIRMED, OrderStatus::PENDING, OrderStatus::PENDING, OrderStatus::CANCELLED, OrderStatus::REFUNDED,
        ];

        for ($i = 1; $i <= 35; $i++) {
            $orderNumber = 'TZ-' . now()->format('Ym') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            if (Order::withoutGlobalScopes()->where('order_number', $orderNumber)->exists()) {
                continue;
            }

            $customer = $this->customers[array_rand($this->customers)];
            $status = $statuses[array_rand($statuses)];
            $daysAgo = rand(1, 60);
            $placedAt = now()->subDays($daysAgo);

            $paymentStatus = match ($status) {
                OrderStatus::DELIVERED, OrderStatus::SHIPPED => PaymentStatus::COMPLETED,
                OrderStatus::PROCESSING, OrderStatus::CONFIRMED => rand(0, 1) ? PaymentStatus::COMPLETED : PaymentStatus::PENDING,
                OrderStatus::CANCELLED => PaymentStatus::CANCELLED,
                OrderStatus::REFUNDED => PaymentStatus::REFUNDED,
                default => PaymentStatus::PENDING,
            };

            $order = Order::withoutGlobalScopes()->create([
                'order_number' => $orderNumber, 'user_id' => $customer->id, 'status' => $status,
                'payment_status' => $paymentStatus, 'payment_method' => PaymentMethodEnum::cases()[array_rand(PaymentMethodEnum::cases())],
                'currency' => 'USD', 'shipping_amount' => rand(0, 1) ? 0 : 3.50, 'tax_amount' => 0, 'subtotal' => 0, 'total' => 0,
                'placed_at' => $placedAt, 'tenant_id' => $this->tid,
                'billing_address' => ['first_name' => explode(' ', $customer->name)[0], 'address_line_1' => 'Quito, Ecuador', 'city' => 'Quito', 'country' => 'EC'],
                'shipping_address' => ['first_name' => explode(' ', $customer->name)[0], 'address_line_1' => 'Quito, Ecuador', 'city' => 'Quito', 'country' => 'EC'],
            ]);

            $numItems = rand(1, 4);
            $orderProducts = collect($productArr)->random(min($numItems, count($productArr)));
            $subtotal = 0;
            foreach ($orderProducts as $product) {
                $qty = rand(1, 3);
                $itemSub = $product->price * $qty;
                $subtotal += $itemSub;
                $order->items()->create(['product_id' => $product->id, 'name' => $product->name, 'sku' => $product->sku, 'price' => $product->price, 'quantity' => $qty, 'subtotal' => $itemSub]);
            }

            $tax = round($subtotal * 0.15, 2);
            $order->update(['subtotal' => $subtotal, 'tax_amount' => $tax, 'total' => $subtotal + $tax + $order->shipping_amount]);

            OrderStatusHistory::create(['order_id' => $order->id, 'user_id' => $this->admin->id, 'old_status' => null, 'new_status' => OrderStatus::PENDING->value, 'notes' => 'Pedido creado']);

            if (in_array($status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED])) {
                $order->update(['tracking_number' => 'SRV-' . rand(100000, 999999), 'shipped_at' => $placedAt->copy()->addDays(rand(1, 3))]);
            }
            if ($status === OrderStatus::DELIVERED) {
                $order->update(['delivered_at' => $placedAt->copy()->addDays(rand(3, 7))]);
            }

            if ($paymentStatus === PaymentStatus::COMPLETED) {
                Payment::create(['order_id' => $order->id, 'transaction_id' => 'TXN-' . Str::upper(Str::random(12)), 'gateway' => 'manual', 'method' => 'bank_transfer', 'amount' => $order->total, 'currency' => 'USD', 'status' => PaymentStatus::COMPLETED, 'paid_at' => $placedAt->copy()->addHours(rand(1, 48)), 'tenant_id' => $this->tid]);
            }
        }
    }

    private function createQuotations(): void
    {
        $productArr = array_values($this->products);
        if (empty($productArr) || empty($this->customers)) {
            return;
        }

        $quotations = [
            ['customer' => 0, 'status' => QuotationStatus::Approved], ['customer' => 3, 'status' => QuotationStatus::Converted],
            ['customer' => 5, 'status' => QuotationStatus::Pending], ['customer' => 14, 'status' => QuotationStatus::Pending],
            ['customer' => 14, 'status' => QuotationStatus::Approved], ['customer' => 7, 'status' => QuotationStatus::Rejected],
            ['customer' => 10, 'status' => QuotationStatus::Draft], ['customer' => 2, 'status' => QuotationStatus::Expired],
        ];

        foreach ($quotations as $i => $q) {
            $customer = $this->customers[$q['customer']] ?? $this->customers[0];
            $number = 'TZ-COT-' . now()->format('Ym') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            if (Quotation::withoutGlobalScopes()->where('quotation_number', $number)->exists()) {
                continue;
            }

            $quotation = Quotation::withoutGlobalScopes()->create([
                'user_id' => $customer->id, 'quotation_number' => $number, 'status' => $q['status'],
                'customer_name' => $customer->name, 'customer_email' => $customer->email, 'customer_phone' => $customer->phone,
                'currency' => 'USD', 'subtotal' => 0, 'tax_amount' => 0, 'total' => 0,
                'valid_until' => now()->addDays(15), 'placed_at' => now()->subDays(rand(1, 20)), 'tenant_id' => $this->tid,
            ]);

            $items = collect($productArr)->random(rand(2, 5));
            $subtotal = 0;
            foreach ($items as $product) {
                $qty = rand(1, 10);
                $itemSub = $product->price * $qty;
                $subtotal += $itemSub;
                $quotation->items()->create(['product_id' => $product->id, 'name' => $product->name, 'sku' => $product->sku, 'price' => $product->price, 'quantity' => $qty, 'subtotal' => $itemSub]);
            }

            $tax = round($subtotal * 0.15, 2);
            $quotation->update(['subtotal' => $subtotal, 'tax_amount' => $tax, 'total' => $subtotal + $tax]);

            if (in_array($q['status'], [QuotationStatus::Approved, QuotationStatus::Converted])) {
                $quotation->update(['approved_by' => $this->admin->id, 'approved_at' => now()->subDays(rand(1, 5))]);
            }
            if ($q['status'] === QuotationStatus::Rejected) {
                $quotation->update(['rejected_by' => $this->admin->id, 'rejected_at' => now()->subDays(2), 'rejection_reason' => 'Presupuesto excede el limite autorizado.']);
            }
        }
    }

    private function createReviews(): void
    {
        $comments = [
            5 => ['Excelente producto, llego antes de lo esperado.', 'Increible calidad, supero mis expectativas.', 'Perfecto estado, bien empacado.'],
            4 => ['Muy buen producto, cumple con lo prometido.', 'Buena relacion calidad-precio.', 'Entrega rapida a Guayaquil.'],
            3 => ['Producto decente, esperaba un poco mas.', 'Esta bien para uso basico.'],
            2 => ['Tuve problemas con la configuracion inicial.'],
            1 => ['Llego con un defecto, pero me hicieron el cambio.'],
        ];

        foreach (array_values($this->products) as $product) {
            for ($j = 0; $j < rand(0, 4); $j++) {
                $rating = $this->weightedRating();
                $customer = $this->customers[array_rand($this->customers)];
                if (Review::withoutGlobalScopes()->where('product_id', $product->id)->where('user_id', $customer->id)->exists()) {
                    continue;
                }

                $commentList = $comments[$rating];
                Review::withoutGlobalScopes()->create([
                    'product_id' => $product->id, 'user_id' => $customer->id, 'rating' => $rating,
                    'title' => $rating >= 4 ? 'Muy satisfecho' : ($rating >= 3 ? 'Aceptable' : 'Podria mejorar'),
                    'comment' => $commentList[array_rand($commentList)],
                    'is_verified_purchase' => rand(0, 3) > 0, 'is_approved' => rand(0, 5) > 0,
                    'is_featured' => $rating === 5 && rand(0, 2) === 0, 'approved_at' => now()->subDays(rand(1, 30)),
                    'admin_response' => $rating <= 2 ? 'Gracias por su feedback. Hemos tomado nota para mejorar.' : null,
                    'tenant_id' => $this->tid,
                ]);
            }
        }
    }

    private function createWishlists(): void
    {
        $productArr = array_values($this->products);
        foreach ($this->customers as $customer) {
            if (rand(0, 2) === 0) {
                continue;
            }
            foreach (collect($productArr)->random(rand(1, 5)) as $product) {
                Wishlist::withoutGlobalScopes()->firstOrCreate(['user_id' => $customer->id, 'product_id' => $product->id], ['tenant_id' => $this->tid]);
            }
        }
    }

    private function createCarts(): void
    {
        $productArr = array_values($this->products);
        for ($i = 0; $i < 5; $i++) {
            $customer = $this->customers[array_rand($this->customers)];
            if (Cart::withoutGlobalScopes()->where('user_id', $customer->id)->exists()) {
                continue;
            }

            $cart = Cart::withoutGlobalScopes()->create(['user_id' => $customer->id, 'expires_at' => now()->addDays(rand(1, 7)), 'reminder_count' => rand(0, 2), 'tenant_id' => $this->tid]);
            foreach (collect($productArr)->random(rand(1, 3)) as $product) {
                CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => rand(1, 2), 'price' => $product->price]);
            }
        }
    }

    private function createInventoryMovements(): void
    {
        $warehouse = $this->warehouses[0] ?? null;
        foreach ($this->products as $product) {
            InventoryMovement::withoutGlobalScopes()->firstOrCreate(
                ['product_id' => $product->id, 'type' => 'initial_balance', 'tenant_id' => $this->tid],
                ['quantity' => $product->quantity + rand(10, 50), 'balance_quantity' => $product->quantity, 'unit_cost' => $product->cost ?? $product->price * 0.6, 'total_cost' => ($product->cost ?? $product->price * 0.6) * $product->quantity, 'user_id' => $this->admin->id, 'notes' => 'Stock inicial', 'warehouse_location_id' => $warehouse?->id, 'tenant_id' => $this->tid]
            );
        }
    }

    private function createStockAlerts(): void
    {
        foreach (['TZ-GPU-001', 'TZ-GAM-002', 'TZ-LAP-003'] as $sku) {
            $product = $this->products[$sku] ?? null;
            if (! $product) {
                continue;
            }
            StockAlert::withoutGlobalScopes()->firstOrCreate(
                ['product_id' => $product->id, 'type' => 'low_stock', 'tenant_id' => $this->tid],
                ['threshold' => $product->low_stock_threshold, 'current_quantity' => $product->quantity, 'status' => 'pending', 'warehouse_location_id' => $this->warehouses[0]?->id, 'tenant_id' => $this->tid]
            );
        }
    }

    private function createStockTransfers(): void
    {
        if (count($this->warehouses) < 2) {
            return;
        }

        $transfers = [
            ['from' => 0, 'to' => 1, 'status' => 'completed', 'skus' => ['TZ-PHN-001', 'TZ-PHN-002']],
            ['from' => 0, 'to' => 2, 'status' => 'in_transit', 'skus' => ['TZ-LAP-005', 'TZ-PER-001']],
        ];

        foreach ($transfers as $i => $t) {
            $number = 'TZ-TRF-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            if (StockTransfer::withoutGlobalScopes()->where('transfer_number', $number)->exists()) {
                continue;
            }

            $transfer = StockTransfer::withoutGlobalScopes()->create([
                'transfer_number' => $number, 'from_location_id' => $this->warehouses[$t['from']]->id, 'to_location_id' => $this->warehouses[$t['to']]->id,
                'status' => $t['status'], 'created_by' => $this->admin->id, 'approved_by' => $this->admin->id,
                'completed_at' => $t['status'] === 'completed' ? now()->subDays(5) : null, 'notes' => 'Reabastecimiento punto de venta', 'tenant_id' => $this->tid,
            ]);

            foreach ($t['skus'] as $sku) {
                $product = $this->products[$sku] ?? null;
                if ($product) {
                    StockTransferItem::create(['stock_transfer_id' => $transfer->id, 'product_id' => $product->id, 'quantity' => rand(3, 10)]);
                }
            }
        }
    }

    private function createProductReturns(): void
    {
        $returns = [
            ['reason' => ReturnReason::Defective, 'status' => ReturnStatus::Refunded, 'desc' => 'Pantalla con pixeles muertos.'],
            ['reason' => ReturnReason::WrongItem, 'status' => ReturnStatus::Exchanged, 'desc' => 'Modelo equivocado de teclado.'],
            ['reason' => ReturnReason::NotAsDescribed, 'status' => ReturnStatus::Approved, 'desc' => 'Especificaciones no coinciden.'],
            ['reason' => ReturnReason::ChangedMind, 'status' => ReturnStatus::Requested, 'desc' => 'Decidi otro modelo. Sin abrir.'],
            ['reason' => ReturnReason::Damaged, 'status' => ReturnStatus::ItemReceived, 'desc' => 'Caja aplastada, producto golpeado.'],
        ];

        $orders = Order::withoutGlobalScopes()->where('tenant_id', $this->tid)->where('status', OrderStatus::DELIVERED)->with('items')->take(5)->get();
        foreach ($returns as $i => $r) {
            $order = $orders[$i] ?? $orders->first();
            if (! $order) {
                continue;
            }

            $number = 'TZ-DEV-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            if (ProductReturn::withoutGlobalScopes()->where('return_number', $number)->exists()) {
                continue;
            }

            $return = ProductReturn::withoutGlobalScopes()->create([
                'order_id' => $order->id, 'user_id' => $order->user_id, 'return_number' => $number,
                'status' => $r['status'], 'reason' => $r['reason'], 'description' => $r['desc'],
                'refund_amount' => $r['status'] === ReturnStatus::Refunded ? $order->items->first()?->subtotal : null,
                'approved_at' => in_array($r['status'], [ReturnStatus::Approved, ReturnStatus::ItemReceived, ReturnStatus::Refunded, ReturnStatus::Exchanged]) ? now()->subDays(3) : null,
                'tenant_id' => $this->tid,
            ]);

            if ($order->items->isNotEmpty()) {
                $item = $order->items->first();
                $return->items()->create(['order_item_id' => $item->id, 'product_id' => $item->product_id, 'quantity' => 1, 'condition' => $r['reason'] === ReturnReason::ChangedMind ? 'new' : 'damaged']);
            }
        }
    }

    private function createSupportTickets(): void
    {
        $tickets = [
            ['subject' => 'Laptop no enciende despues de actualizacion', 'status' => TicketStatus::Resolved, 'priority' => TicketPriority::High, 'category' => 'soporte_tecnico'],
            ['subject' => 'Consulta sobre garantia extendida', 'status' => TicketStatus::Closed, 'priority' => TicketPriority::Low, 'category' => 'garantia'],
            ['subject' => 'Pedido no ha llegado (5 dias)', 'status' => TicketStatus::InProgress, 'priority' => TicketPriority::High, 'category' => 'envio'],
            ['subject' => 'Error al aplicar cupon BIENVENIDO15', 'status' => TicketStatus::Resolved, 'priority' => TicketPriority::Medium, 'category' => 'facturacion'],
            ['subject' => 'Solicitud de factura electronica', 'status' => TicketStatus::Closed, 'priority' => TicketPriority::Low, 'category' => 'facturacion'],
            ['subject' => 'Mouse dejo de funcionar (2 semanas)', 'status' => TicketStatus::WaitingOnCustomer, 'priority' => TicketPriority::Medium, 'category' => 'garantia'],
            ['subject' => 'Asesoria para armar PC gaming', 'status' => TicketStatus::Open, 'priority' => TicketPriority::Low, 'category' => 'ventas'],
            ['subject' => 'Cobro doble en mi tarjeta', 'status' => TicketStatus::InProgress, 'priority' => TicketPriority::Urgent, 'category' => 'facturacion'],
            ['subject' => 'Monitor llego con pantalla rota', 'status' => TicketStatus::Open, 'priority' => TicketPriority::Urgent, 'category' => 'devolucion'],
            ['subject' => 'Compatibilidad RAM con motherboard', 'status' => TicketStatus::Resolved, 'priority' => TicketPriority::Low, 'category' => 'soporte_tecnico'],
        ];

        foreach ($tickets as $i => $t) {
            $customer = $this->customers[$i % count($this->customers)];
            $number = 'TZ-TKT-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            if (SupportTicket::withoutGlobalScopes()->where('ticket_number', $number)->exists()) {
                continue;
            }

            $ticket = SupportTicket::withoutGlobalScopes()->create([
                'user_id' => $customer->id, 'ticket_number' => $number, 'subject' => $t['subject'],
                'status' => $t['status'], 'priority' => $t['priority'], 'category' => $t['category'],
                'assigned_to' => $t['status'] !== TicketStatus::Open ? $this->admin->id : null,
                'resolved_at' => in_array($t['status'], [TicketStatus::Resolved, TicketStatus::Closed]) ? now()->subDays(rand(1, 5)) : null,
                'closed_at' => $t['status'] === TicketStatus::Closed ? now()->subDays(1) : null,
                'tenant_id' => $this->tid,
            ]);

            TicketMessage::create(['support_ticket_id' => $ticket->id, 'user_id' => $customer->id, 'message' => "Hola, necesito ayuda: {$t['subject']}", 'is_from_admin' => false]);
            if ($t['status'] !== TicketStatus::Open) {
                TicketMessage::create(['support_ticket_id' => $ticket->id, 'user_id' => $this->admin->id, 'message' => "Estimado/a, estamos revisando su caso.", 'is_from_admin' => true]);
            }
        }
    }

    private function createBanners(): void
    {
        $banners = [
            ['title' => 'MacBook Pro M3 Max', 'subtitle' => 'Potencia profesional. Desde $3,499', 'button_text' => 'Ver Laptops', 'button_url' => '/shop?category=laptops', 'position' => 'hero'],
            ['title' => 'Gaming Week', 'subtitle' => 'Hasta 25% OFF en perifericos gaming', 'button_text' => 'Comprar', 'button_url' => '/shop?category=gaming', 'position' => 'hero'],
            ['title' => 'Arma tu PC', 'subtitle' => 'CPUs, GPUs y RAM al mejor precio', 'button_text' => 'Componentes', 'button_url' => '/shop?category=componentes-pc', 'position' => 'hero'],
        ];

        foreach ($banners as $i => $b) {
            Banner::withoutGlobalScopes()->firstOrCreate(
                ['title' => $b['title'], 'tenant_id' => $this->tid],
                array_merge($b, ['sort_order' => $i, 'is_active' => true, 'starts_at' => now()->subMonth(), 'expires_at' => now()->addMonths(3), 'tenant_id' => $this->tid])
            );
        }
    }

    private function createHomepageSections(): void
    {
        $sections = [
            ['type' => SectionType::Hero, 'name' => 'Banner Principal', 'sort_order' => 1, 'config' => ['autoplay' => true, 'interval' => 5000]],
            ['type' => SectionType::CategoryStrip, 'name' => 'Categorias Destacadas', 'sort_order' => 2, 'config' => ['max_items' => 8]],
            ['type' => SectionType::ProductGrid, 'name' => 'Lo Mas Vendido', 'sort_order' => 3, 'config' => ['title' => 'Lo Mas Vendido', 'filter' => 'featured', 'limit' => 8]],
            ['type' => SectionType::BrandSlider, 'name' => 'Nuestras Marcas', 'sort_order' => 4, 'config' => ['speed' => 3000]],
            ['type' => SectionType::ValueProps, 'name' => 'Valores TechZone', 'sort_order' => 5, 'config' => ['items' => [
                ['icon' => 'truck', 'title' => 'Envio a Todo Ecuador', 'description' => 'Gratis en compras +$200'],
                ['icon' => 'shield-check', 'title' => 'Garantia 12 Meses', 'description' => 'En todos los productos'],
                ['icon' => 'credit-card', 'title' => 'Pago Seguro', 'description' => 'Multiples metodos'],
                ['icon' => 'headphones', 'title' => 'Soporte Tecnico', 'description' => 'Asistencia especializada'],
            ]]],
        ];

        foreach ($sections as $s) {
            HomepageSection::withoutGlobalScopes()->firstOrCreate(
                ['name' => $s['name'], 'tenant_id' => $this->tid],
                array_merge($s, ['is_active' => true, 'tenant_id' => $this->tid])
            );
        }
    }

    private function createProductBundles(): void
    {
        $bundles = [
            ['name' => 'Pack Productividad Home Office', 'desc' => 'Todo para trabajar desde casa.', 'price' => 749.00, 'compare' => 857.00, 'skus' => ['TZ-LAP-005', 'TZ-PER-001', 'TZ-PER-003', 'TZ-WEB-001']],
            ['name' => 'Bundle Gaming Starter', 'desc' => 'Empieza tu setup gaming.', 'price' => 449.00, 'compare' => 557.00, 'skus' => ['TZ-PER-002', 'TZ-PER-004', 'TZ-AUD-001']],
            ['name' => 'Kit PC Gamer Definitivo', 'desc' => 'CPU + GPU + RAM + SSD + PSU.', 'price' => 2799.00, 'compare' => 3066.00, 'skus' => ['TZ-CPU-001', 'TZ-GPU-001', 'TZ-RAM-001', 'TZ-SSD-001', 'TZ-PSU-001']],
        ];

        foreach ($bundles as $b) {
            $bundle = ProductBundle::withoutGlobalScopes()->firstOrCreate(
                ['slug' => Str::slug($b['name']), 'tenant_id' => $this->tid],
                ['name' => $b['name'], 'description' => $b['desc'], 'price' => $b['price'], 'compare_price' => $b['compare'], 'is_active' => true, 'starts_at' => now()->subWeek(), 'ends_at' => now()->addMonths(2), 'tenant_id' => $this->tid]
            );

            foreach ($b['skus'] as $sku) {
                $product = $this->products[$sku] ?? null;
                if ($product) {
                    $bundle->items()->firstOrCreate(['product_id' => $product->id], ['quantity' => 1]);
                }
            }
        }
    }

    private function createEmailCampaigns(): void
    {
        $campaigns = [
            ['name' => 'Bienvenida Nuevos Clientes', 'subject' => 'Bienvenido a TechZone - 15% en tu primera compra', 'status' => CampaignStatus::Sent, 'segment' => 'new_customers', 'recipients' => 245, 'sent' => 240, 'opened' => 156, 'clicked' => 89],
            ['name' => 'Newsletter Marzo 2026', 'subject' => 'Lo nuevo: RTX 4090, iPhone 16 y mas', 'status' => CampaignStatus::Sent, 'segment' => 'all', 'recipients' => 1200, 'sent' => 1180, 'opened' => 412, 'clicked' => 178],
            ['name' => 'Carritos Abandonados', 'subject' => 'Olvidaste algo en tu carrito', 'status' => CampaignStatus::Sending, 'segment' => 'abandoned_cart', 'recipients' => 85, 'sent' => 42, 'opened' => 18, 'clicked' => 7],
            ['name' => 'Promo Dia del Padre', 'subject' => 'Regalos tech para Papa - Hasta 30% OFF', 'status' => CampaignStatus::Scheduled, 'segment' => 'all', 'recipients' => 1500, 'sent' => 0, 'opened' => 0, 'clicked' => 0],
            ['name' => 'Black Friday Early Access', 'subject' => 'Acceso anticipado Black Friday VIP', 'status' => CampaignStatus::Draft, 'segment' => 'vip', 'recipients' => 0, 'sent' => 0, 'opened' => 0, 'clicked' => 0],
        ];

        foreach ($campaigns as $c) {
            EmailCampaign::withoutGlobalScopes()->firstOrCreate(
                ['name' => $c['name'], 'tenant_id' => $this->tid],
                [
                    'subject' => $c['subject'], 'content' => '<h1>' . $c['subject'] . '</h1><p>Contenido del email TechZone.</p>',
                    'status' => $c['status'], 'segment' => $c['segment'],
                    'recipients_count' => $c['recipients'], 'sent_count' => $c['sent'], 'opened_count' => $c['opened'], 'clicked_count' => $c['clicked'],
                    'bounced_count' => (int) ($c['recipients'] * 0.02), 'unsubscribed_count' => (int) ($c['opened'] * 0.01),
                    'scheduled_at' => $c['status'] === CampaignStatus::Scheduled ? now()->addDays(75) : null,
                    'sent_at' => $c['status'] === CampaignStatus::Sent ? now()->subDays(rand(1, 30)) : null,
                    'tenant_id' => $this->tid,
                ]
            );
        }
    }

    private function createPages(): void
    {
        $pages = [
            [
                'title' => 'Quienes Somos',
                'slug' => 'quienes-somos',
                'meta_title' => 'Quienes Somos - TechZone Ecuador',
                'meta_description' => 'Conoce a TechZone Ecuador, tu tienda de tecnologia de confianza con los mejores productos y servicio.',
                'sort_order' => 1,
                'content' => [
                    [
                        'type' => 'hero_banner',
                        'data' => [
                            'heading' => 'Somos TechZone Ecuador',
                            'subheading' => 'Tu aliado en tecnologia desde 2018. Mas de 5 anos llevando lo mejor en hardware, laptops y accesorios a todo el pais.',
                            'cta_text' => 'Ver Catalogo',
                            'cta_url' => '/shop',
                            'background_image' => null,
                        ],
                    ],
                    [
                        'type' => 'features_grid',
                        'data' => [
                            'columns' => 3,
                            'features' => [
                                ['icon' => 'shield', 'title' => 'Garantia Oficial', 'description' => 'Todos nuestros productos cuentan con garantia de fabrica respaldada por TechZone.'],
                                ['icon' => 'truck', 'title' => 'Envio a Todo el Pais', 'description' => 'Llegamos a las 24 provincias del Ecuador con envios seguros y rastreables.'],
                                ['icon' => 'support', 'title' => 'Soporte Tecnico', 'description' => 'Equipo de expertos disponible para asesorarte en tu compra y post-venta.'],
                                ['icon' => 'star', 'title' => 'Productos Originales', 'description' => 'Trabajamos directamente con distribuidores autorizados de las mejores marcas.'],
                                ['icon' => 'check-circle', 'title' => 'Precios Competitivos', 'description' => 'Ofrecemos los mejores precios del mercado ecuatoriano sin comprometer calidad.'],
                                ['icon' => 'heart', 'title' => 'Clientes Satisfechos', 'description' => 'Mas de 10,000 clientes confian en nosotros para sus compras de tecnologia.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'content' => '<h2>Nuestra Historia</h2><p>TechZone nacio en 2018 con una vision clara: democratizar el acceso a tecnologia de calidad en Ecuador. Comenzamos como una pequena tienda en Quito y hoy somos una de las plataformas de ecommerce de tecnologia mas grandes del pais.</p><p>Nuestro equipo esta conformado por apasionados de la tecnologia que entienden las necesidades de cada cliente, desde el gamer que busca el mejor rendimiento hasta el profesional que necesita herramientas confiables para su trabajo diario.</p>',
                        ],
                    ],
                    [
                        'type' => 'call_to_action',
                        'data' => [
                            'heading' => 'Tienes preguntas? Estamos para ayudarte',
                            'description' => 'Nuestro equipo de soporte esta disponible de lunes a sabado para asesorarte.',
                            'button_text' => 'Contactar Soporte',
                            'button_url' => '/shop',
                            'bg_color' => null,
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Nuestros Servicios',
                'slug' => 'nuestros-servicios',
                'meta_title' => 'Servicios - TechZone Ecuador',
                'meta_description' => 'Descubre todos los servicios que TechZone ofrece: envio, soporte tecnico, garantia y mas.',
                'sort_order' => 2,
                'content' => [
                    [
                        'type' => 'hero_banner',
                        'data' => [
                            'heading' => 'Nuestros Servicios',
                            'subheading' => 'Mas que una tienda, somos tu socio tecnologico integral.',
                            'cta_text' => null,
                            'cta_url' => null,
                            'background_image' => null,
                        ],
                    ],
                    [
                        'type' => 'features_grid',
                        'data' => [
                            'columns' => 2,
                            'features' => [
                                ['icon' => 'truck', 'title' => 'Envio Express', 'description' => 'Recibe tu pedido en 24-48 horas en ciudades principales. Envio gratuito en compras mayores a $150.'],
                                ['icon' => 'shield', 'title' => 'Garantia Extendida', 'description' => 'Ofrecemos planes de garantia extendida de hasta 3 anos en productos seleccionados.'],
                                ['icon' => 'support', 'title' => 'Armado de PCs', 'description' => 'Te ayudamos a armar tu PC gaming o workstation con los mejores componentes segun tu presupuesto.'],
                                ['icon' => 'globe', 'title' => 'Importacion bajo Pedido', 'description' => 'No encuentras lo que buscas? Importamos productos especificos directamente para ti.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'spacer',
                        'data' => ['height' => 'medium'],
                    ],
                    [
                        'type' => 'testimonials',
                        'data' => [
                            'items' => [
                                ['name' => 'Carlos Mendoza', 'role' => 'Gamer', 'quote' => 'Arme mi PC gaming completa con la asesoria de TechZone. Excelente servicio y precios.', 'avatar' => null],
                                ['name' => 'Maria Fernanda Lopez', 'role' => 'Disenadora Grafica', 'quote' => 'Compre mi MacBook y el servicio post-venta ha sido impecable. 100% recomendados.', 'avatar' => null],
                                ['name' => 'Roberto Alvarado', 'role' => 'Empresa PYME', 'quote' => 'TechZone equipo toda nuestra oficina. Facturacion electronica rapida y envio puntual.', 'avatar' => null],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Preguntas Frecuentes',
                'slug' => 'preguntas-frecuentes',
                'meta_title' => 'Preguntas Frecuentes - TechZone Ecuador',
                'meta_description' => 'Resuelve tus dudas sobre compras, envios, garantias y devoluciones en TechZone.',
                'sort_order' => 3,
                'content' => [
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'content' => '<h2>Preguntas Frecuentes</h2><p>Aqui encontraras respuestas a las preguntas mas comunes sobre nuestros productos y servicios.</p>',
                        ],
                    ],
                    [
                        'type' => 'faq',
                        'data' => [
                            'items' => [
                                ['question' => 'Como puedo realizar una compra?', 'answer' => 'Navega nuestro catalogo, agrega los productos al carrito y sigue el proceso de checkout. Aceptamos tarjetas de credito, transferencia bancaria y pago contra entrega.'],
                                ['question' => 'Cuanto tiempo tarda el envio?', 'answer' => 'Para Quito y Guayaquil el envio es de 24-48 horas. Para otras ciudades, entre 3-5 dias habiles. El envio es gratuito en compras mayores a $150.'],
                                ['question' => 'Que garantia tienen los productos?', 'answer' => 'Todos nuestros productos tienen garantia de fabrica. Adicionalmente, ofrecemos planes de garantia extendida de 1, 2 y 3 anos.'],
                                ['question' => 'Puedo devolver un producto?', 'answer' => 'Si, tienes 30 dias para devolver un producto en su empaque original. Aplican condiciones segun el tipo de producto.'],
                                ['question' => 'Hacen factura electronica?', 'answer' => 'Si, emitimos factura electronica autorizada por el SRI. Solo necesitas proporcionarnos tu cedula o RUC al momento de la compra.'],
                                ['question' => 'Ofrecen servicio de armado de computadoras?', 'answer' => 'Si, tenemos un servicio de armado profesional. Puedes seleccionar los componentes y nosotros nos encargamos del ensamblaje, pruebas y configuracion inicial.'],
                                ['question' => 'Puedo solicitar una cotizacion?', 'answer' => 'Claro, tenemos un sistema de cotizaciones online. Agrega productos a tu cotizacion y la recibiras por email con validez de 15 dias.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'call_to_action',
                        'data' => [
                            'heading' => 'No encontraste tu respuesta?',
                            'description' => 'Contactanos directamente y te ayudaremos con gusto.',
                            'button_text' => 'Ir al Soporte',
                            'button_url' => '/shop',
                            'bg_color' => '#1e3a5f',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            Page::withoutGlobalScopes()->firstOrCreate(
                ['slug' => $pageData['slug'], 'tenant_id' => $this->tid],
                [
                    'title' => $pageData['title'],
                    'content' => $pageData['content'],
                    'meta_title' => $pageData['meta_title'],
                    'meta_description' => $pageData['meta_description'],
                    'is_published' => true,
                    'published_at' => now(),
                    'sort_order' => $pageData['sort_order'],
                    'tenant_id' => $this->tid,
                ]
            );
        }
    }

    private function weightedRating(): int
    {
        $rand = rand(1, 100);
        if ($rand <= 40) {
            return 5;
        }
        if ($rand <= 70) {
            return 4;
        }
        if ($rand <= 85) {
            return 3;
        }
        if ($rand <= 95) {
            return 2;
        }

        return 1;
    }
}
