<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('is_default', true)->first();

        if (! $tenant) {
            $this->command->warn('No se encontro un tenant por defecto. Marque un tenant como is_default=true.');

            return;
        }

        app()->instance('current_tenant', $tenant);

        // 1. Categories
        $categories = [
            'Computadoras',
            'Repuestos para Laptop',
            'Componentes PC',
            'Video y Vigilancia',
            'Software y Servicios',
            'General',
        ];

        foreach ($categories as $categoryName) {
            Category::withoutGlobalScopes()->firstOrCreate(
                ['slug' => Str::slug($categoryName), 'tenant_id' => $tenant->id],
                [
                    'name' => $categoryName,
                    'is_active' => true,
                    'is_visible_in_menu' => true,
                    'tenant_id' => $tenant->id,
                ]
            );
        }

        // 2. Brands
        $brands = [
            'ASUS' => ['color' => '#00539b', 'text' => '#ffffff'],
            'ACER' => ['color' => '#83b81a', 'text' => '#ffffff'],
            'HP' => ['color' => '#0096d6', 'text' => '#ffffff'],
            'DELL' => ['color' => '#007db8', 'text' => '#ffffff'],
            'LENOVO' => ['color' => '#e2231a', 'text' => '#ffffff'],
            'SAMSUNG' => ['color' => '#1428a0', 'text' => '#ffffff'],
            'KINGSTON' => ['color' => '#c00', 'text' => '#ffffff'],
            'LOGITECH' => ['color' => '#00b8fc', 'text' => '#000000'],
        ];

        // Ensure temp directory exists
        if (! file_exists(storage_path('app/temp_logos'))) {
            mkdir(storage_path('app/temp_logos'), 0755, true);
        }

        foreach ($brands as $brandName => $colors) {
            $brand = Brand::withoutGlobalScopes()->firstOrCreate(
                ['slug' => Str::slug($brandName), 'tenant_id' => $tenant->id],
                [
                    'name' => $brandName,
                    'is_active' => true,
                    'tenant_id' => $tenant->id,
                ]
            );

            // Generate SVG
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 200">
  <rect width="100%" height="100%" rx="20" fill="{$colors['color']}"/>
  <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="60" fill="{$colors['text']}">{$brandName}</text>
</svg>
SVG;
            $tempPath = storage_path("app/temp_logos/{$brandName}.svg");
            file_put_contents($tempPath, $svg);

            // Always update logo
            try {
                $brand->clearMediaCollection('logo');
                $brand->addMedia($tempPath)
                    ->preservingOriginal()
                    ->toMediaCollection('logo');
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        // 3. Products (Sample data)
        $products = [
            [
                'name' => 'ASUS 65W TIPO C',
                'price' => 45.00,
                'brand' => 'ASUS',
                'category' => 'Repuestos para Laptop',
                'image' => 'https://placehold.co/400x400?text=Product',
                'specs' => ['Power' => '65W', 'Connector' => 'Type-C'],
            ],
            [
                'name' => 'ASUS 150W 19V 6,32A 4.5*3.0mm',
                'price' => 70.00,
                'brand' => 'ASUS',
                'category' => 'Repuestos para Laptop',
                'image' => 'https://placehold.co/400x400?text=Product',
                'specs' => ['Power' => '150W', 'Voltage' => '19V', 'Amperage' => '6.32A', 'Connector' => '4.5*3.0mm'],
            ],
            [
                'name' => 'ASUS 240W 20V 12A 6.0*3.7mm',
                'price' => 115.00,
                'brand' => 'ASUS',
                'category' => 'Repuestos para Laptop',
                'image' => 'https://placehold.co/400x400?text=Product',
                'specs' => ['Power' => '240W', 'Voltage' => '20V', 'Amperage' => '12A', 'Connector' => '6.0*3.7mm'],
            ],
            [
                'name' => 'ACER 135W 19V 7.1A 5.5*1.7mm',
                'price' => 75.00,
                'brand' => 'ACER',
                'category' => 'Repuestos para Laptop',
                'image' => 'https://placehold.co/400x400?text=Product',
                'specs' => ['Power' => '135W', 'Voltage' => '19V', 'Amperage' => '7.1A', 'Connector' => '5.5*1.7mm'],
            ],
            [
                'name' => 'ACER 180W 19V 9.23A 5.5*1.7mm',
                'price' => 95.00,
                'brand' => 'ACER',
                'category' => 'Repuestos para Laptop',
                'image' => 'https://placehold.co/400x400?text=Product',
                'specs' => ['Power' => '180W', 'Voltage' => '19V', 'Amperage' => '9.23A', 'Connector' => '5.5*1.7mm'],
            ],
        ];

        foreach ($products as $data) {
            $category = Category::withoutGlobalScopes()
                ->where('slug', Str::slug($data['category']))
                ->where('tenant_id', $tenant->id)
                ->first();

            $brand = Brand::withoutGlobalScopes()
                ->where('slug', Str::slug($data['brand']))
                ->where('tenant_id', $tenant->id)
                ->first();

            $product = Product::withoutGlobalScopes()->updateOrCreate(
                ['slug' => Str::slug($data['name']), 'tenant_id' => $tenant->id],
                [
                    'name' => $data['name'],
                    'sku' => Str::upper(Str::random(8)),
                    'description' => $this->formatSpecs($data['specs']),
                    'short_description' => $data['name'],
                    'price' => $data['price'],
                    'category_id' => $category?->id,
                    'brand_id' => $brand?->id,
                    'quantity' => rand(10, 100),
                    'is_active' => true,
                    'is_featured' => rand(0, 1),
                    'specifications' => $data['specs'],
                    'tenant_id' => $tenant->id,
                ]
            );

            // Add Image if not already present
            if ($product->getMedia('images')->isEmpty()) {
                try {
                    $product->addMediaFromUrl($data['image'])
                        ->toMediaCollection('images');
                } catch (\Exception $e) {
                    // Ignore image download errors (e.g. 404)
                }
            }
        }

        // 4. Customers & Orders
        $users = \App\Models\User::factory(10)->create(['tenant_id' => $tenant->id]);

        // Create 20 random orders
        foreach (range(1, 20) as $i) {
            $orderNumber = 'CP-SEED-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            if (\App\Models\Order::withoutGlobalScopes()->where('order_number', $orderNumber)->exists()) {
                continue;
            }

            $user = $users->random();
            $status = \App\Enums\OrderStatus::cases()[array_rand(\App\Enums\OrderStatus::cases())];

            $order = \App\Models\Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'status' => $status,
                'payment_status' => $status === \App\Enums\OrderStatus::DELIVERED || $status === \App\Enums\OrderStatus::SHIPPED ? \App\Enums\PaymentStatus::COMPLETED : \App\Enums\PaymentStatus::PENDING,
                'payment_method' => \App\Enums\PaymentMethod::cases()[array_rand(\App\Enums\PaymentMethod::cases())],
                'currency' => 'USD',
                'shipping_amount' => 5.00,
                'tax_amount' => 0,
                'subtotal' => 0,
                'total' => 0,
                'tenant_id' => $tenant->id,
                'billing_address' => [
                    'first_name' => $user->name,
                    'address_line_1' => '123 Fake St',
                    'city' => 'Quito',
                    'country' => 'EC',
                ],
                'shipping_address' => [
                    'first_name' => $user->name,
                    'address_line_1' => '123 Fake St',
                    'city' => 'Quito',
                    'country' => 'EC',
                ],
            ]);

            // Add 1-3 random items
            $randomProducts = Product::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->inRandomOrder()
                ->take(rand(1, 3))
                ->get();

            foreach ($randomProducts as $product) {
                $qty = rand(1, 2);
                $order->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $product->price * $qty,
                ]);
            }

            $order->calculateTotals();
        }
    }

    private function formatSpecs(array $specs): string
    {
        $html = '<ul>';
        foreach ($specs as $key => $value) {
            $html .= "<li><strong>{$key}:</strong> {$value}</li>";
        }
        $html .= '</ul>';

        return $html;
    }
}
