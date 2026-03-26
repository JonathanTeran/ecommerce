<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoStoreSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('is_default', true)->first();

        if (! $tenant) {
            $this->command->error('No default tenant found.');

            return;
        }

        app()->instance('current_tenant', $tenant);

        // Reset admin password
        $admin = User::withoutGlobalScopes()->where('email', 'admin@ecommerce.com')->first();
        if ($admin) {
            $admin->update(['password' => Hash::make('Admin2026!')]);
        }

        // Create demo customer
        Role::firstOrCreate(['name' => 'customer']);
        $customer = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'cliente@demo.com'],
            [
                'name' => 'Carlos Mendoza',
                'password' => Hash::make('Cliente2026!'),
                'tenant_id' => $tenant->id,
                'email_verified_at' => now(),
            ]
        );
        $customer->assignRole('customer');

        // Create additional demo customers
        $customers = collect();
        $demoCustomers = [
            ['name' => 'María Fernández', 'email' => 'maria@demo.com'],
            ['name' => 'José Ruiz', 'email' => 'jose@demo.com'],
            ['name' => 'Ana Torres', 'email' => 'ana@demo.com'],
            ['name' => 'Luis Paredes', 'email' => 'luis@demo.com'],
            ['name' => 'Gabriela Mora', 'email' => 'gabriela@demo.com'],
        ];

        foreach ($demoCustomers as $dc) {
            $u = User::withoutGlobalScopes()->updateOrCreate(
                ['email' => $dc['email']],
                [
                    'name' => $dc['name'],
                    'password' => Hash::make('Cliente2026!'),
                    'tenant_id' => $tenant->id,
                    'email_verified_at' => now(),
                ]
            );
            $u->assignRole('customer');
            $customers->push($u);
        }
        $customers->push($customer);

        // Get categories
        $catMap = Category::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->pluck('id', 'name');

        // Get brands
        $brandMap = Brand::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->pluck('id', 'name');

        // Ensure brands exist
        $brandsToCreate = ['ASUS', 'ACER', 'HP', 'DELL', 'LENOVO', 'APPLE', 'SAMSUNG', 'KINGSTON', 'LOGITECH', 'TP-LINK', 'EPSON', 'CANON', 'CORSAIR', 'INTEL', 'AMD', 'WESTERN DIGITAL', 'SEAGATE', 'HIKVISION'];
        foreach ($brandsToCreate as $brandName) {
            if (! $brandMap->has($brandName)) {
                $b = Brand::withoutGlobalScopes()->create([
                    'name' => $brandName,
                    'slug' => Str::slug($brandName),
                    'is_active' => true,
                    'tenant_id' => $tenant->id,
                ]);
                $brandMap[$brandName] = $b->id;
            }
        }

        // Products data — realistic electronics/parts store
        $products = [
            // Laptops
            ['name' => 'Laptop ASUS VivoBook 15 i5-1235U 8GB 512GB SSD', 'cat' => 'Notebook - Laptop', 'brand' => 'ASUS', 'price' => 649.99, 'cost' => 520, 'desc' => 'Laptop ASUS VivoBook 15 con procesador Intel Core i5 12va gen, 8GB RAM DDR4, 512GB SSD NVMe, pantalla 15.6" Full HD IPS. Ideal para trabajo y estudio.', 'short' => 'Laptop ASUS i5 12va gen, 8GB, 512GB SSD, 15.6" FHD', 'qty' => 15, 'featured' => true, 'new' => true],
            ['name' => 'Laptop HP 250 G9 i3-1215U 8GB 256GB SSD', 'cat' => 'Notebook - Laptop', 'brand' => 'HP', 'price' => 449.99, 'cost' => 360, 'desc' => 'Laptop HP 250 G9 con Intel Core i3 12va gen, 8GB RAM, 256GB SSD, pantalla 15.6" HD. Perfecta para oficina y tareas cotidianas.', 'short' => 'Laptop HP i3, 8GB, 256GB SSD, 15.6"', 'qty' => 20, 'featured' => true, 'new' => false],
            ['name' => 'Laptop Lenovo IdeaPad 3 Ryzen 5 5500U 12GB 512GB', 'cat' => 'Notebook - Laptop', 'brand' => 'LENOVO', 'price' => 579.99, 'cost' => 470, 'desc' => 'Laptop Lenovo IdeaPad 3 con AMD Ryzen 5 5500U, 12GB RAM DDR4, 512GB SSD NVMe, pantalla 15.6" Full HD. Rendimiento superior para multitarea.', 'short' => 'Lenovo IdeaPad Ryzen 5, 12GB, 512GB SSD', 'qty' => 12, 'featured' => true, 'new' => true],
            ['name' => 'MacBook Air M2 8GB 256GB SSD Space Gray', 'cat' => 'Notebook - Laptop', 'brand' => 'APPLE', 'price' => 1099.00, 'cost' => 950, 'desc' => 'Apple MacBook Air con chip M2, 8GB RAM unificada, 256GB SSD, pantalla Liquid Retina 13.6". Delgado, potente y con batería de todo el día.', 'short' => 'MacBook Air M2, 8GB, 256GB, 13.6" Retina', 'qty' => 8, 'featured' => true, 'new' => true],
            ['name' => 'Laptop Dell Inspiron 15 i5-1335U 16GB 512GB SSD', 'cat' => 'Notebook - Laptop', 'brand' => 'DELL', 'price' => 729.99, 'cost' => 590, 'desc' => 'Dell Inspiron 15 con Intel Core i5 13va gen, 16GB RAM DDR4, 512GB SSD NVMe, pantalla 15.6" FHD antirreflejo. Diseño elegante y potente.', 'short' => 'Dell Inspiron i5 13va, 16GB, 512GB SSD', 'qty' => 10, 'featured' => false, 'new' => true],

            // SSD & Storage
            ['name' => 'SSD Kingston A400 480GB SATA 2.5"', 'cat' => 'Disco Duro Sólido (SSD)', 'brand' => 'KINGSTON', 'price' => 42.99, 'cost' => 32, 'desc' => 'Disco SSD Kingston A400 de 480GB con interfaz SATA III 2.5". Velocidades de lectura hasta 500MB/s. Mejora el rendimiento de tu laptop o PC.', 'short' => 'SSD Kingston 480GB SATA 2.5"', 'qty' => 50, 'featured' => true, 'new' => false],
            ['name' => 'SSD Samsung 980 NVMe M.2 1TB', 'cat' => 'Disco Duro Sólido (SSD)', 'brand' => 'SAMSUNG', 'price' => 89.99, 'cost' => 68, 'desc' => 'SSD Samsung 980 de 1TB con interfaz NVMe M.2 PCIe 3.0. Velocidades de lectura hasta 3,500MB/s. Almacenamiento ultrarrápido para gaming y producción.', 'short' => 'SSD Samsung 980 NVMe 1TB', 'qty' => 30, 'featured' => true, 'new' => false],
            ['name' => 'Disco Duro Externo WD Elements 1TB USB 3.0', 'cat' => 'Disco Duro Externo', 'brand' => 'WESTERN DIGITAL', 'price' => 55.00, 'cost' => 42, 'desc' => 'Disco duro externo WD Elements de 1TB con USB 3.0. Compatible con PC y Mac. Portátil y confiable para respaldos.', 'short' => 'HDD Externo WD 1TB USB 3.0', 'qty' => 25, 'featured' => false, 'new' => false],
            ['name' => 'Disco Duro Externo Seagate Expansion 2TB USB 3.0', 'cat' => 'Disco Duro Externo', 'brand' => 'SEAGATE', 'price' => 72.00, 'cost' => 55, 'desc' => 'Disco duro portátil Seagate Expansion de 2TB con USB 3.0. Plug and play, ideal para almacenar archivos grandes, fotos y videos.', 'short' => 'HDD Externo Seagate 2TB USB 3.0', 'qty' => 18, 'featured' => false, 'new' => false],
            ['name' => 'MicroSD Samsung EVO Plus 128GB Class 10 U3', 'cat' => 'Memoria Micro SD', 'brand' => 'SAMSUNG', 'price' => 18.50, 'cost' => 12, 'desc' => 'Tarjeta MicroSD Samsung EVO Plus de 128GB con velocidad de lectura hasta 130MB/s. Clase 10, U3. Ideal para cámaras, drones y smartphones.', 'short' => 'MicroSD Samsung 128GB Class 10', 'qty' => 60, 'featured' => false, 'new' => false],
            ['name' => 'Pen Drive Kingston DataTraveler 64GB USB 3.2', 'cat' => 'Pen Drive', 'brand' => 'KINGSTON', 'price' => 9.99, 'cost' => 6, 'desc' => 'Memoria USB Kingston DataTraveler de 64GB con USB 3.2 Gen 1. Velocidades de lectura hasta 200MB/s. Diseño compacto con tapa deslizante.', 'short' => 'USB Kingston 64GB 3.2', 'qty' => 80, 'featured' => false, 'new' => false],

            // RAM
            ['name' => 'Memoria RAM Kingston Fury 8GB DDR4 3200MHz Laptop', 'cat' => 'Memorias (Laptop/PC)', 'brand' => 'KINGSTON', 'price' => 28.00, 'cost' => 20, 'desc' => 'Memoria RAM Kingston Fury Impact de 8GB DDR4 3200MHz SO-DIMM para laptop. Plug & Play, aumenta el rendimiento de tu portátil.', 'short' => 'RAM DDR4 8GB 3200MHz Laptop', 'qty' => 40, 'featured' => true, 'new' => false],
            ['name' => 'Memoria RAM Corsair Vengeance 16GB (2x8GB) DDR4 3200MHz PC', 'cat' => 'Memorias (Laptop/PC)', 'brand' => 'CORSAIR', 'price' => 52.00, 'cost' => 38, 'desc' => 'Kit de memoria Corsair Vengeance LPX 16GB (2x8GB) DDR4 3200MHz DIMM para PC. Disipador térmico de aluminio, XMP 2.0.', 'short' => 'RAM DDR4 16GB (2x8) 3200MHz PC', 'qty' => 25, 'featured' => true, 'new' => false],

            // Peripherals
            ['name' => 'Mouse Logitech MX Master 3S Inalámbrico', 'cat' => 'Mouse', 'brand' => 'LOGITECH', 'price' => 89.99, 'cost' => 70, 'desc' => 'Mouse ergonómico Logitech MX Master 3S con sensor 8000 DPI, clicks silenciosos, scroll MagSpeed, conexión Bluetooth y USB. Compatible con 3 dispositivos.', 'short' => 'Mouse Logitech MX Master 3S Wireless', 'qty' => 15, 'featured' => true, 'new' => true],
            ['name' => 'Teclado Logitech K380 Bluetooth Multi-Dispositivo', 'cat' => 'Teclados para PC', 'brand' => 'LOGITECH', 'price' => 39.99, 'cost' => 28, 'desc' => 'Teclado compacto Logitech K380 con Bluetooth, compatible con Windows, macOS, iPadOS, ChromeOS y Android. Hasta 3 dispositivos simultáneos.', 'short' => 'Teclado Bluetooth Logitech K380', 'qty' => 20, 'featured' => false, 'new' => false],
            ['name' => 'Audífonos Samsung Galaxy Buds FE', 'cat' => 'Audífonos', 'brand' => 'SAMSUNG', 'price' => 79.99, 'cost' => 58, 'desc' => 'Audífonos inalámbricos Samsung Galaxy Buds FE con cancelación activa de ruido, sonido AKG, resistencia al agua IPX2, batería de hasta 30 horas.', 'short' => 'Samsung Galaxy Buds FE ANC', 'qty' => 22, 'featured' => true, 'new' => true],
            ['name' => 'Webcam Logitech C920 HD Pro 1080p', 'cat' => 'Cámara Web', 'brand' => 'LOGITECH', 'price' => 69.99, 'cost' => 50, 'desc' => 'Cámara web Logitech C920 HD Pro con video Full HD 1080p a 30fps, micrófono estéreo, corrección de luz HD. Ideal para videollamadas y streaming.', 'short' => 'Webcam Logitech C920 1080p', 'qty' => 12, 'featured' => false, 'new' => false],

            // Networking
            ['name' => 'Router TP-Link Archer AX23 WiFi 6 AX1800', 'cat' => 'Routers', 'brand' => 'TP-LINK', 'price' => 59.99, 'cost' => 44, 'desc' => 'Router TP-Link Archer AX23 con WiFi 6 AX1800, doble banda, 4 antenas, OFDMA, MU-MIMO, hasta 1.8 Gbps. Cobertura para casas medianas.', 'short' => 'Router WiFi 6 TP-Link AX1800', 'qty' => 18, 'featured' => true, 'new' => false],
            ['name' => 'Switch TP-Link 8 Puertos Gigabit TL-SG108', 'cat' => 'Switch', 'brand' => 'TP-LINK', 'price' => 22.00, 'cost' => 15, 'desc' => 'Switch de escritorio TP-Link de 8 puertos Gigabit Ethernet 10/100/1000 Mbps. Plug and play, carcasa metálica, silencioso.', 'short' => 'Switch 8 puertos Gigabit TP-Link', 'qty' => 30, 'featured' => false, 'new' => false],

            // Printers
            ['name' => 'Impresora Epson EcoTank L3250 WiFi Multifuncional', 'cat' => 'Impresoras', 'brand' => 'EPSON', 'price' => 259.99, 'cost' => 210, 'desc' => 'Impresora multifuncional Epson EcoTank L3250 con WiFi, sistema de tinta continua, impresión, copia y escaneo. Rendimiento de hasta 7,500 páginas en negro.', 'short' => 'Epson L3250 WiFi tinta continua', 'qty' => 10, 'featured' => true, 'new' => false],
            ['name' => 'Impresora Canon PIXMA G3160 WiFi Multifuncional', 'cat' => 'Impresoras', 'brand' => 'CANON', 'price' => 239.99, 'cost' => 195, 'desc' => 'Impresora multifuncional Canon PIXMA G3160 con WiFi y sistema de tinta recargable MegaTank. Hasta 6,000 páginas en negro y 7,700 en color.', 'short' => 'Canon G3160 WiFi MegaTank', 'qty' => 8, 'featured' => false, 'new' => false],

            // Security
            ['name' => 'Kit 4 Cámaras Hikvision 1080p + DVR 4CH', 'cat' => 'Cámara de Seguridad', 'brand' => 'HIKVISION', 'price' => 189.99, 'cost' => 140, 'desc' => 'Kit de videovigilancia Hikvision con 4 cámaras tipo bala 1080p, DVR de 4 canales, fuentes de poder y cables. Visión nocturna hasta 20m. IP66.', 'short' => 'Kit 4 cámaras Hikvision 1080p + DVR', 'qty' => 7, 'featured' => true, 'new' => false],
            ['name' => 'Cámara IP Hikvision WiFi 2MP 360° Interior', 'cat' => 'Cámara de Seguridad', 'brand' => 'HIKVISION', 'price' => 45.00, 'cost' => 30, 'desc' => 'Cámara IP Hikvision de 2MP con WiFi, giro 360°, detección de movimiento, audio bidireccional, visión nocturna, ranura MicroSD. App Hik-Connect.', 'short' => 'Cámara WiFi Hikvision 2MP 360°', 'qty' => 25, 'featured' => false, 'new' => true],

            // PC Components
            ['name' => 'Procesador Intel Core i5-13400F 10 núcleos LGA1700', 'cat' => 'Procesadores', 'brand' => 'INTEL', 'price' => 189.99, 'cost' => 155, 'desc' => 'Procesador Intel Core i5-13400F de 13va generación, 10 núcleos (6P+4E), 16 hilos, hasta 4.6GHz Turbo Boost, socket LGA1700. Sin gráficos integrados.', 'short' => 'Intel i5-13400F 10C/16T LGA1700', 'qty' => 10, 'featured' => true, 'new' => false],
            ['name' => 'Procesador AMD Ryzen 5 5600 6 núcleos AM4', 'cat' => 'Procesadores', 'brand' => 'AMD', 'price' => 139.99, 'cost' => 110, 'desc' => 'Procesador AMD Ryzen 5 5600 con 6 núcleos, 12 hilos, hasta 4.4GHz boost, 35MB caché, socket AM4. Incluye cooler Wraith Stealth.', 'short' => 'AMD Ryzen 5 5600 6C/12T AM4', 'qty' => 12, 'featured' => true, 'new' => false],
            ['name' => 'Tarjeta de Video MSI GeForce RTX 4060 Ventus 2X 8GB', 'cat' => 'Tarjetas de video', 'brand' => 'ASUS', 'price' => 329.99, 'cost' => 270, 'desc' => 'Tarjeta gráfica MSI GeForce RTX 4060 Ventus 2X con 8GB GDDR6, DLSS 3, Ray Tracing, reloj boost 2460MHz. Ideal para gaming 1080p.', 'short' => 'RTX 4060 8GB GDDR6', 'qty' => 6, 'featured' => true, 'new' => true],
            ['name' => 'Fuente de Poder Corsair CV550 550W 80+ Bronze', 'cat' => 'Fuentes de poder', 'brand' => 'CORSAIR', 'price' => 54.99, 'cost' => 40, 'desc' => 'Fuente de poder Corsair CV550 de 550W con certificación 80 Plus Bronze, ventilador de 120mm, protección contra sobrevoltaje. ATX 2.4.', 'short' => 'PSU Corsair 550W 80+ Bronze', 'qty' => 15, 'featured' => false, 'new' => false],

            // Cargadores adicionales
            ['name' => 'Cargador HP 65W 19.5V 3.33A 4.5*3.0mm', 'cat' => 'Cargadores', 'brand' => 'HP', 'price' => 35.00, 'cost' => 22, 'desc' => 'Cargador compatible para laptops HP con conector 4.5*3.0mm azul. 65W, 19.5V, 3.33A. Compatible con HP Pavilion, ProBook, EliteBook.', 'short' => 'Cargador HP 65W blue tip', 'qty' => 40, 'featured' => false, 'new' => false],
            ['name' => 'Cargador Dell 65W USB-C Tipo C', 'cat' => 'Cargadores', 'brand' => 'DELL', 'price' => 45.00, 'cost' => 30, 'desc' => 'Cargador Dell original de 65W con conector USB-C. Compatible con Dell Latitude, XPS y otras laptops con carga USB-C.', 'short' => 'Cargador Dell 65W USB-C', 'qty' => 25, 'featured' => false, 'new' => false],
            ['name' => 'Cargador Lenovo 65W USB-C ThinkPad', 'cat' => 'Cargadores', 'brand' => 'LENOVO', 'price' => 40.00, 'cost' => 28, 'desc' => 'Cargador Lenovo de 65W USB-C compatible con ThinkPad, IdeaPad y Yoga. Compacto, ligero y de carga rápida.', 'short' => 'Cargador Lenovo 65W USB-C', 'qty' => 30, 'featured' => false, 'new' => false],

            // Baterías
            ['name' => 'Batería HP HS04 14.8V 2670mAh 4 celdas', 'cat' => 'Baterías', 'brand' => 'HP', 'price' => 38.00, 'cost' => 25, 'desc' => 'Batería de reemplazo HP HS04 de 14.8V 2670mAh, 4 celdas. Compatible con HP 240 G4, 245 G4, 250 G4, 255 G4, Pavilion 14/15.', 'short' => 'Batería HP HS04 4 celdas', 'qty' => 20, 'featured' => false, 'new' => false],

            // UPS
            ['name' => 'UPS APC Back-UPS 600VA 7 Tomas', 'cat' => 'UPS', 'brand' => 'ACER', 'price' => 65.00, 'cost' => 48, 'desc' => 'UPS APC Back-UPS de 600VA/330W con 7 tomas, protección contra sobretensiones, regulación automática de voltaje. Batería de respaldo para PC.', 'short' => 'UPS APC 600VA 7 tomas', 'qty' => 12, 'featured' => false, 'new' => false],
        ];

        $createdProducts = collect();

        foreach ($products as $p) {
            $catId = $catMap[$p['cat']] ?? null;
            $brandId = $brandMap[$p['brand']] ?? null;

            if (! $catId) {
                continue;
            }

            $product = Product::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $p['name']],
                [
                    'sku' => Str::upper(Str::random(8)),
                    'category_id' => $catId,
                    'brand_id' => $brandId,
                    'price' => $p['price'],
                    'compare_price' => $p['featured'] ? round($p['price'] * 1.15, 2) : null,
                    'cost' => $p['cost'],
                    'quantity' => $p['qty'],
                    'description' => $p['desc'],
                    'short_description' => $p['short'],
                    'is_active' => true,
                    'is_featured' => $p['featured'],
                    'is_new' => $p['new'],
                    'low_stock_threshold' => 5,
                    'warranty_months' => $p['price'] > 200 ? 12 : 6,
                ]
            );

            $createdProducts->push($product);
        }

        $this->command->info("Created {$createdProducts->count()} products.");

        // Coupons
        Coupon::withoutGlobalScopes()->updateOrCreate(
            ['code' => 'BIENVENIDO10', 'tenant_id' => $tenant->id],
            [
                'name' => 'Bienvenida 10%',
                'description' => '10% de descuento en tu primera compra',
                'type' => 'percentage',
                'value' => 10,
                'min_order_amount' => 50,
                'max_discount_amount' => 100,
                'usage_limit' => 500,
                'usage_count' => 43,
                'starts_at' => now()->subMonth(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ]
        );

        Coupon::withoutGlobalScopes()->updateOrCreate(
            ['code' => 'ENVIOGRATIS', 'tenant_id' => $tenant->id],
            [
                'name' => 'Envío Gratis',
                'description' => 'Envío gratis en compras mayores a $100',
                'type' => 'free_shipping',
                'value' => 0,
                'min_order_amount' => 100,
                'usage_limit' => 200,
                'usage_count' => 15,
                'starts_at' => now()->subWeeks(2),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ]
        );

        $this->command->info('Coupons created.');

        // Reviews on popular products
        $reviewTexts = [
            ['t' => 'Excelente producto', 'c' => 'Llegó rápido y funciona perfectamente. Muy buena calidad por el precio.', 'r' => 5],
            ['t' => 'Muy bueno', 'c' => 'Cumple con lo prometido. El rendimiento es muy bueno para el uso diario.', 'r' => 4],
            ['t' => 'Buena relación calidad-precio', 'c' => 'Para lo que cuesta está bastante bien. Lo recomiendo.', 'r' => 4],
            ['t' => 'Superó mis expectativas', 'c' => 'Pensé que sería regular pero resultó ser un producto de muy buena calidad. Muy contento.', 'r' => 5],
            ['t' => 'Buen producto', 'c' => 'Funciona bien, la instalación fue sencilla. Buen servicio.', 'r' => 4],
            ['t' => 'Recomendado', 'c' => 'Lo compré para mi oficina y ha funcionado sin problemas. Entrega puntual.', 'r' => 5],
            ['t' => 'Aceptable', 'c' => 'Cumple su función. El empaque podría mejorar pero el producto está bien.', 'r' => 3],
            ['t' => 'Perfecto para gaming', 'c' => 'Excelente rendimiento en juegos. Temperaturas bajas y silencioso.', 'r' => 5],
        ];

        $featuredProducts = $createdProducts->where('is_featured', true)->take(10);
        $reviewIndex = 0;

        foreach ($featuredProducts as $product) {
            $numReviews = rand(2, 4);
            $shuffledCustomers = $customers->shuffle();

            for ($i = 0; $i < $numReviews && $i < $shuffledCustomers->count(); $i++) {
                $r = $reviewTexts[$reviewIndex % count($reviewTexts)];
                Review::withoutGlobalScopes()->updateOrCreate(
                    ['product_id' => $product->id, 'user_id' => $shuffledCustomers[$i]->id, 'tenant_id' => $tenant->id],
                    [
                        'rating' => $r['r'],
                        'title' => $r['t'],
                        'comment' => $r['c'],
                        'is_verified_purchase' => true,
                        'is_approved' => true,
                    ]
                );
                $reviewIndex++;
            }
        }

        // Update average ratings
        foreach ($featuredProducts as $product) {
            $avg = Review::withoutGlobalScopes()->where('product_id', $product->id)->avg('rating');
            $count = Review::withoutGlobalScopes()->where('product_id', $product->id)->count();
            $product->update(['average_rating' => round($avg, 1), 'reviews_count' => $count]);
        }

        $this->command->info("Reviews created for {$featuredProducts->count()} products.");
        $this->command->info('Demo store seeded successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Rol', 'Email', 'Contraseña'],
            [
                ['Super Admin', 'superadmin@amephia.com', 'password'],
                ['Admin Tienda', 'admin@ecommerce.com', 'Admin2026!'],
                ['Cliente Demo', 'cliente@demo.com', 'Cliente2026!'],
            ]
        );
    }
}
