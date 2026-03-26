<?php

use App\Enums\Module;
use App\Enums\OrderStatus;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\GeneralSetting;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// =====================================================================
// HELPERS
// =====================================================================

function buildTenant(string $slug, float $taxRate = 15.00): array
{
    $tenant = Tenant::factory()->create(['slug' => $slug, 'name' => ucfirst($slug)]);
    $plan = Plan::firstOrCreate(
        ['slug' => 'enterprise'],
        ['name' => 'Enterprise', 'type' => 'enterprise', 'price' => 150,
            'modules' => array_map(fn (Module $m) => $m->value, Module::cases()), 'is_active' => true]
    );
    Subscription::create([
        'tenant_id' => $tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    GeneralSetting::create([
        'tenant_id' => $tenant->id, 'site_name' => ucfirst($slug), 'tax_rate' => $taxRate,
        'payment_gateways_config' => [
            'bank_transfer_enabled' => true,
            'bank_transfer_requires_proof' => false,
            'bank_transfer_surcharge_percentage' => 0,
        ],
    ]);
    $category = Category::withoutGlobalScopes()->create([
        'name' => "Cat-{$slug}", 'slug' => "cat-{$slug}", 'tenant_id' => $tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $brand = Brand::withoutGlobalScopes()->create([
        'name' => "Brand-{$slug}", 'slug' => "brand-{$slug}", 'tenant_id' => $tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);

    return compact('tenant', 'category', 'brand');
}

function buildProduct(int $tenantId, int $categoryId, int $brandId, string $name, float $price, int $stock): Product
{
    return Product::withoutGlobalScopes()->create([
        'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name).'-'.uniqid(),
        'sku' => 'SKU-'.strtoupper(uniqid()), 'price' => $price, 'quantity' => $stock,
        'is_active' => true, 'tenant_id' => $tenantId,
        'category_id' => $categoryId, 'brand_id' => $brandId,
    ]);
}

function orderPayload(string $paymentMethod = 'bank_transfer'): array
{
    return [
        'payment_method' => $paymentMethod,
        'shipping_address' => [
            'name' => 'Buyer '.uniqid(), 'identity_document' => '17'.rand(10000000, 99999999),
            'email' => 'buyer'.uniqid().'@test.com', 'address' => 'Calle '.rand(1, 200),
            'city' => 'Quito', 'state' => 'Pichincha', 'zip' => '170150', 'phone' => '09'.rand(10000000, 99999999),
        ],
        'billing_address' => [
            'name' => 'Buyer Factura', 'tax_id' => '17'.rand(10000000, 99999999).'001',
            'address' => 'Calle '.rand(1, 200), 'city' => 'Quito',
            'state' => 'Pichincha', 'zip' => '170150', 'phone' => '09'.rand(10000000, 99999999),
        ],
        'accepted_legal_documents' => true,
    ];
}

/**
 * Execute a full purchase: add to cart -> place order -> return order
 */
function executePurchase($test, Tenant $tenant, User $buyer, Product $product, string $paymentMethod = 'bank_transfer', int $qty = 1): Order
{
    app()->instance('current_tenant', $tenant);
    $test->actingAs($buyer);

    // Create cart and item directly in database (avoids HTTP session state issues in tests)
    $cart = Cart::withoutGlobalScopes()->create([
        'user_id' => $buyer->id,
        'session_id' => (string) \Illuminate\Support\Str::uuid(),
        'tenant_id' => $tenant->id,
        'expires_at' => now()->addDays(30),
    ]);

    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => $qty,
        'price' => $product->price,
        'tenant_id' => $tenant->id,
    ]);

    $sid = $cart->session_id;

    $response = $test->withHeaders(['X-Session-ID' => $sid, 'Accept' => 'application/json'])
        ->postJson('/checkout/place-order', orderPayload($paymentMethod));

    $response->assertSuccessful();
    $orderId = $response->json('order_id');

    return Order::withoutGlobalScopes()->findOrFail($orderId);
}

// =====================================================================
// TEST: 5 tenants, 3 buyers each, 15 compras simultaneas
// =====================================================================

it('processes 15 purchases across 5 tenants with full isolation', function () {
    $tenantSlugs = ['electronica-ec', 'moda-store', 'deportes-pro', 'hogar-feliz', 'tech-gadgets'];
    $tenants = [];
    $products = [];
    $buyers = [];
    $initialStocks = [];

    // ── SETUP: 5 tenants, cada uno con 2 productos, 3 buyers ──
    foreach ($tenantSlugs as $i => $slug) {
        $t = buildTenant($slug, taxRate: 12.00 + $i);
        $tenants[$slug] = $t;

        $products[$slug] = [
            buildProduct($t['tenant']->id, $t['category']->id, $t['brand']->id,
                "Producto Principal {$slug}", 100.00 + ($i * 50), 100),
            buildProduct($t['tenant']->id, $t['category']->id, $t['brand']->id,
                "Producto Secundario {$slug}", 50.00 + ($i * 25), 200),
        ];

        $initialStocks[$slug] = [
            $products[$slug][0]->quantity,
            $products[$slug][1]->quantity,
        ];

        $buyers[$slug] = [];
        for ($b = 0; $b < 3; $b++) {
            $buyers[$slug][] = User::factory()->create([
                'tenant_id' => $t['tenant']->id,
                'email' => "buyer{$b}@{$slug}.com",
            ]);
        }
    }

    // ── EXECUTE: 15 purchases (3 per tenant) ──
    $allOrders = [];
    foreach ($tenantSlugs as $slug) {
        $t = $tenants[$slug];

        // Buyer 0: compra 2 del producto principal
        $allOrders["{$slug}-0"] = executePurchase(
            $this, $t['tenant'], $buyers[$slug][0], $products[$slug][0], qty: 2
        );

        // Buyer 1: compra 1 del producto secundario
        $allOrders["{$slug}-1"] = executePurchase(
            $this, $t['tenant'], $buyers[$slug][1], $products[$slug][1], qty: 1
        );

        // Buyer 2: compra 3 del producto principal
        $allOrders["{$slug}-2"] = executePurchase(
            $this, $t['tenant'], $buyers[$slug][2], $products[$slug][0], qty: 3
        );
    }

    // ── VERIFY: 15 ordenes creadas ──
    $totalOrders = Order::withoutGlobalScopes()->count();
    expect($totalOrders)->toBe(15);

    // ── VERIFY: cada tenant tiene exactamente 3 ordenes ──
    foreach ($tenantSlugs as $slug) {
        app()->instance('current_tenant', $tenants[$slug]['tenant']);
        $tenantOrders = Order::all();
        expect($tenantOrders)->toHaveCount(3);

        // Cada orden pertenece al tenant correcto
        $tenantOrders->each(function ($order) use ($tenants, $slug) {
            expect($order->tenant_id)->toBe($tenants[$slug]['tenant']->id);
        });
    }

    // ── VERIFY: stock deducido correctamente por tenant ──
    foreach ($tenantSlugs as $slug) {
        $p0 = $products[$slug][0]->fresh();
        $p1 = $products[$slug][1]->fresh();

        // Producto principal: buyer0 compro 2, buyer2 compro 3 = 5 unidades
        expect($p0->quantity)->toBe($initialStocks[$slug][0] - 5);

        // Producto secundario: buyer1 compro 1 = 1 unidad
        expect($p1->quantity)->toBe($initialStocks[$slug][1] - 1);
    }

    // ── VERIFY: movimientos de inventario correctos ──
    $totalMovements = InventoryMovement::withoutGlobalScopes()->where('type', 'sale')->count();
    expect($totalMovements)->toBe(15); // 1 movimiento por orden

    // ── VERIFY: aislamiento total - cada tenant NO ve ordenes de otros ──
    foreach ($tenantSlugs as $slug) {
        app()->instance('current_tenant', $tenants[$slug]['tenant']);
        $orders = Order::all();
        $uniqueTenantIds = $orders->pluck('tenant_id')->unique();
        expect($uniqueTenantIds)->toHaveCount(1)
            ->and($uniqueTenantIds->first())->toBe($tenants[$slug]['tenant']->id);
    }

    // ── VERIFY: items de orden correctos ──
    foreach ($tenantSlugs as $slug) {
        $order0 = $allOrders["{$slug}-0"];
        $items0 = \App\Models\OrderItem::withoutGlobalScopes()->where('order_id', $order0->id)->get();
        expect($items0)->toHaveCount(1)
            ->and($items0->first()->quantity)->toBe(2);

        $order2 = $allOrders["{$slug}-2"];
        $items2 = \App\Models\OrderItem::withoutGlobalScopes()->where('order_id', $order2->id)->get();
        expect($items2)->toHaveCount(1)
            ->and($items2->first()->quantity)->toBe(3);
    }

    // ── VERIFY: no hay carritos pendientes (todos limpiados) ──
    expect(Cart::withoutGlobalScopes()->count())->toBe(0);
});

// =====================================================================
// TEST: 10 tenants, 1 buyer cada uno - compra masiva de un solo producto
// =====================================================================

it('processes 10 tenant purchases each buying the same quantity', function () {
    $orders = [];

    for ($i = 0; $i < 10; $i++) {
        $t = buildTenant("tenant-{$i}");
        $product = buildProduct($t['tenant']->id, $t['category']->id, $t['brand']->id,
            "Item {$i}", 75.00, stock: 50);
        $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);

        $orders[] = executePurchase($this, $t['tenant'], $buyer, $product, qty: 5);

        // Stock deducido
        expect($product->fresh()->quantity)->toBe(45);
    }

    // 10 ordenes totales
    expect(Order::withoutGlobalScopes()->count())->toBe(10);

    // Cada orden tiene cantidad correcta
    foreach ($orders as $order) {
        $items = \App\Models\OrderItem::withoutGlobalScopes()->where('order_id', $order->id)->get();
        expect($items->first()->quantity)->toBe(5)
            ->and($order->status)->toBe(OrderStatus::PENDING);
    }

    // No quedan carritos
    expect(Cart::withoutGlobalScopes()->count())->toBe(0);
});

// =====================================================================
// TEST: Tenant con cupones - multiples buyers usan el mismo cupon
// =====================================================================

it('multiple buyers in same tenant use same coupon with usage tracking', function () {
    $t = buildTenant('cupon-store');
    app()->instance('current_tenant', $t['tenant']);

    $product = buildProduct($t['tenant']->id, $t['category']->id, $t['brand']->id,
        'Premium Widget', 200.00, stock: 100);

    $coupon = Coupon::withoutGlobalScopes()->create([
        'code' => 'MASS20', 'name' => 'Mass Discount', 'type' => 'percentage', 'value' => 20,
        'is_active' => true, 'tenant_id' => $t['tenant']->id,
        'usage_limit' => 5, 'usage_count' => 0, 'first_order_only' => false,
        'starts_at' => now()->subDay(), 'expires_at' => now()->addMonth(),
    ]);

    // 5 buyers use the coupon (up to the limit)
    for ($i = 0; $i < 5; $i++) {
        app()->instance('current_tenant', $t['tenant']);
        $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);
        $this->actingAs($buyer);

        $cart = Cart::withoutGlobalScopes()->create([
            'user_id' => $buyer->id,
            'session_id' => (string) \Illuminate\Support\Str::uuid(),
            'tenant_id' => $t['tenant']->id,
            'coupon_id' => $coupon->id,
            'expires_at' => now()->addDays(30),
        ]);
        $cart->items()->create([
            'product_id' => $product->id, 'quantity' => 1,
            'price' => $product->price, 'tenant_id' => $t['tenant']->id,
        ]);

        $this->withHeaders(['X-Session-ID' => $cart->session_id, 'Accept' => 'application/json'])
            ->postJson('/checkout/place-order', orderPayload())
            ->assertSuccessful();
    }

    // Verify coupon used 5 times
    expect($coupon->fresh()->usage_count)->toBe(5);

    // 6th buyer tries to apply exhausted coupon via API
    app()->instance('current_tenant', $t['tenant']);
    $buyer6 = User::factory()->create(['tenant_id' => $t['tenant']->id]);
    $this->actingAs($buyer6);
    $sid6 = (string) \Illuminate\Support\Str::uuid();

    $this->withHeaders(['X-Session-ID' => $sid6])
        ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->withHeaders(['X-Session-ID' => $sid6])
        ->postJson('/api/cart/apply-coupon', ['code' => 'MASS20']);
    $response->assertStatus(422); // Coupon exhausted

    // 5 orders created, stock deducted by 5
    $totalOrders = Order::withoutGlobalScopes()->where('tenant_id', $t['tenant']->id)->count();
    expect($totalOrders)->toBe(5);
    expect($product->fresh()->quantity)->toBe(95);
});

// =====================================================================
// TEST: Tenant con diferentes metodos de pago y recargos
// =====================================================================

it('3 tenants with different payment surcharges calculate totals correctly', function () {
    $configs = [
        ['slug' => 'pay-zero', 'surcharge' => 0, 'price' => 100, 'payment_method' => 'bank_transfer'],
        ['slug' => 'pay-medium', 'surcharge' => 2.5, 'price' => 200, 'payment_method' => 'credit_card'],
        ['slug' => 'pay-high', 'surcharge' => 5.0, 'price' => 500, 'payment_method' => 'credit_card'],
    ];

    foreach ($configs as $cfg) {
        $t = buildTenant($cfg['slug']);

        // Update payment_gateways_config for the tenant's GeneralSetting
        $setting = GeneralSetting::withoutGlobalScopes()->where('tenant_id', $t['tenant']->id)->first();
        $gatewayConfig = $setting->payment_gateways_config ?? [];

        if ($cfg['surcharge'] > 0) {
            $gatewayConfig['nuvei_enabled'] = true;
            $gatewayConfig['nuvei_surcharge_percentage'] = $cfg['surcharge'];
        }

        $setting->update(['payment_gateways_config' => $gatewayConfig]);

        $product = buildProduct($t['tenant']->id, $t['category']->id, $t['brand']->id,
            "Item {$cfg['slug']}", (float) $cfg['price'], stock: 50);
        $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);

        $order = executePurchase($this, $t['tenant'], $buyer, $product, $cfg['payment_method'], qty: 1);

        expect((float) $order->subtotal)->toBe((float) $cfg['price']);

        if ($cfg['surcharge'] > 0) {
            expect((float) $order->surcharge_amount)->toBeGreaterThan(0);
            expect((float) $order->total)->toBeGreaterThan((float) $order->subtotal);
        } else {
            expect((float) $order->surcharge_amount)->toBe(0.0);
        }
    }

    expect(Order::withoutGlobalScopes()->count())->toBe(3);
});
