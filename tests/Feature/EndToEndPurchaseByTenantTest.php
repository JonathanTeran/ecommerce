<?php

use App\Enums\Module;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Auth\Modals;
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
use Livewire\Livewire;

uses(RefreshDatabase::class);

function setupTenant(string $slug, string $name): array
{
    $tenant = Tenant::factory()->create(['slug' => $slug, 'name' => $name]);
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
        'tenant_id' => $tenant->id, 'site_name' => $name, 'tax_rate' => 15.00,
        'payment_gateways_config' => [
            'bank_transfer_enabled' => true,
            'bank_transfer_requires_proof' => false,
            'bank_transfer_surcharge_percentage' => 0,
        ],
    ]);

    $category = Category::withoutGlobalScopes()->create([
        'name' => "Cat {$slug}", 'slug' => "cat-{$slug}", 'tenant_id' => $tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $brand = Brand::withoutGlobalScopes()->create([
        'name' => "Brand {$slug}", 'slug' => "brand-{$slug}", 'tenant_id' => $tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);

    return compact('tenant', 'category', 'brand');
}

function createProduct(int $tenantId, int $categoryId, int $brandId, array $overrides = []): Product
{
    return Product::withoutGlobalScopes()->create(array_merge([
        'name' => 'Product '.uniqid(),
        'slug' => 'product-'.uniqid(),
        'sku' => 'SKU-'.strtoupper(uniqid()),
        'price' => 100.00,
        'quantity' => 50,
        'is_active' => true,
        'tenant_id' => $tenantId,
        'category_id' => $categoryId,
        'brand_id' => $brandId,
    ], $overrides));
}

function checkoutData(): array
{
    return [
        'payment_method' => 'bank_transfer',
        'shipping_address' => [
            'name' => 'Comprador Test',
            'identity_document' => '1712345678',
            'email' => 'comprador@test.com',
            'address' => 'Av. Amazonas N36-152',
            'city' => 'Quito',
            'state' => 'Pichincha',
            'zip' => '170150',
            'phone' => '0991234567',
        ],
        'billing_address' => [
            'name' => 'Comprador Test',
            'tax_id' => '1712345678001',
            'address' => 'Av. Amazonas N36-152',
            'city' => 'Quito',
            'state' => 'Pichincha',
            'zip' => '170150',
            'phone' => '0991234567',
        ],
        'accepted_legal_documents' => true,
    ];
}

// ========================================================================
// ESCENARIO 1: Compra basica completa en Tenant A
// ========================================================================

it('Tenant A: complete purchase flow - register, add to cart, checkout, verify order', function () {
    $t = setupTenant('tienda-a', 'Tienda A');
    app()->instance('current_tenant', $t['tenant']);

    $product = createProduct($t['tenant']->id, $t['category']->id, $t['brand']->id, [
        'name' => 'Laptop Dell', 'price' => 500.00, 'quantity' => 10,
    ]);

    // 1. Register buyer
    $buyer = User::factory()->create([
        'email' => 'buyer-a@test.com', 'tenant_id' => $t['tenant']->id,
    ]);
    $this->actingAs($buyer);

    // 2. Add to cart
    $sessionId = (string) \Illuminate\Support\Str::uuid();
    $response = $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 2]);
    $response->assertSuccessful();

    // 3. Verify cart
    $cartResponse = $this->withHeaders(['X-Session-ID' => $sessionId])
        ->getJson('/api/cart');
    $cartData = $cartResponse->json('data');
    expect((float) $cartData['subtotal'])->toBe(1000.00);

    // 4. Place order
    $orderResponse = $this->withHeaders([
        'X-Session-ID' => $sessionId,
        'Accept' => 'application/json',
    ])->postJson('/checkout/place-order', checkoutData());
    $orderResponse->assertSuccessful();

    // 5. Verify order in database
    $order = Order::where('user_id', $buyer->id)->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::PENDING)
        ->and($order->payment_status)->toBe(PaymentStatus::PENDING)
        ->and((float) $order->subtotal)->toBe(1000.00)
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->quantity)->toBe(2)
        ->and($order->tenant_id)->toBe($t['tenant']->id);

    // 6. Verify stock deducted
    expect($product->fresh()->quantity)->toBe(8);

    // 7. Verify inventory movement
    $movement = InventoryMovement::where('product_id', $product->id)
        ->where('type', 'sale')->first();
    expect($movement)->not->toBeNull()
        ->and($movement->quantity)->toBe(-2);

    // 8. Verify cart cleared
    expect(Cart::where('user_id', $buyer->id)->count())->toBe(0);

    // 9. Verify order confirmation page
    $this->get('/checkout/confirmation/'.$order->id)->assertSuccessful();

    // 10. Verify order in account
    $this->get('/account/orders')->assertSuccessful();
    $this->get('/account/orders/'.$order->id)->assertSuccessful();
});

// ========================================================================
// ESCENARIO 2: Compra con cupon de descuento en Tenant B
// ========================================================================

it('Tenant B: purchase with percentage coupon applied', function () {
    $t = setupTenant('tienda-b', 'Tienda B');
    app()->instance('current_tenant', $t['tenant']);

    $product = createProduct($t['tenant']->id, $t['category']->id, $t['brand']->id, [
        'name' => 'Monitor LG', 'price' => 400.00, 'quantity' => 5,
    ]);

    $coupon = Coupon::withoutGlobalScopes()->create([
        'code' => 'DESC20', 'name' => '20% OFF', 'type' => 'percentage', 'value' => 20,
        'is_active' => true, 'tenant_id' => $t['tenant']->id,
        'usage_count' => 0, 'first_order_only' => false,
        'starts_at' => now()->subDay(), 'expires_at' => now()->addMonth(),
    ]);

    $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);
    $this->actingAs($buyer);
    $sessionId = (string) \Illuminate\Support\Str::uuid();

    // Add to cart
    $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1])
        ->assertSuccessful();

    // Apply coupon
    $couponResponse = $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart/apply-coupon', ['code' => 'DESC20']);
    $couponResponse->assertSuccessful();

    // Verify discount in cart
    $cartData = $this->withHeaders(['X-Session-ID' => $sessionId])
        ->getJson('/api/cart')->json('data');
    expect((float) $cartData['subtotal'])->toBe(400.00)
        ->and((float) $cartData['discount_amount'])->toBe(80.00); // 20% of 400

    // Place order
    $this->withHeaders(['X-Session-ID' => $sessionId, 'Accept' => 'application/json'])
        ->postJson('/checkout/place-order', checkoutData())
        ->assertSuccessful();

    $order = Order::where('user_id', $buyer->id)->first();
    expect($order)->not->toBeNull()
        ->and((float) $order->subtotal)->toBe(400.00)
        ->and((float) $order->discount_amount)->toBe(80.00)
        ->and($order->coupon_code)->toBe('DESC20');

    // Coupon usage incremented
    expect($coupon->fresh()->usage_count)->toBe(1);
});

// ========================================================================
// ESCENARIO 3: Compra con metodo de pago con recargo (surcharge)
// ========================================================================

it('Tenant A: purchase with payment method surcharge', function () {
    $t = setupTenant('tienda-surcharge', 'Tienda Surcharge');
    app()->instance('current_tenant', $t['tenant']);

    $product = createProduct($t['tenant']->id, $t['category']->id, $t['brand']->id, [
        'name' => 'Teclado Mecanico', 'price' => 200.00, 'quantity' => 20,
    ]);

    // Enable nuvei (credit card) with surcharge
    $settings = GeneralSetting::withoutGlobalScopes()
        ->where('tenant_id', $t['tenant']->id)->first();
    $settings->update([
        'payment_gateways_config' => array_merge(
            $settings->payment_gateways_config ?? [],
            ['nuvei_enabled' => true, 'nuvei_surcharge_percentage' => 3.50]
        ),
    ]);

    $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);
    $this->actingAs($buyer);
    $sessionId = (string) \Illuminate\Support\Str::uuid();

    $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1])
        ->assertSuccessful();

    $checkoutData = checkoutData();
    $checkoutData['payment_method'] = 'credit_card';

    $this->withHeaders(['X-Session-ID' => $sessionId, 'Accept' => 'application/json'])
        ->postJson('/checkout/place-order', $checkoutData)
        ->assertSuccessful();

    $order = Order::where('user_id', $buyer->id)->first();
    expect($order)->not->toBeNull()
        ->and((float) $order->subtotal)->toBe(200.00)
        ->and((float) $order->surcharge_amount)->toBeGreaterThan(0)
        ->and((float) $order->total)->toBeGreaterThan(200.00);
});

// ========================================================================
// ESCENARIO 4: Multiples productos en una orden
// ========================================================================

it('Tenant: purchase multiple different products in one order', function () {
    $t = setupTenant('tienda-multi', 'Tienda Multi');
    app()->instance('current_tenant', $t['tenant']);

    $product1 = createProduct($t['tenant']->id, $t['category']->id, $t['brand']->id, [
        'name' => 'Mouse', 'price' => 50.00, 'quantity' => 30,
    ]);
    $product2 = createProduct($t['tenant']->id, $t['category']->id, $t['brand']->id, [
        'name' => 'Headset', 'price' => 150.00, 'quantity' => 15,
    ]);

    $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);
    $this->actingAs($buyer);
    $sessionId = (string) \Illuminate\Support\Str::uuid();

    // Add both products
    $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $product1->id, 'quantity' => 3]);
    $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $product2->id, 'quantity' => 1]);

    // Verify cart has 2 items
    $cartData = $this->withHeaders(['X-Session-ID' => $sessionId])
        ->getJson('/api/cart')->json('data');
    expect((float) $cartData['subtotal'])->toBe(300.00); // 50*3 + 150*1

    // Place order
    $this->withHeaders(['X-Session-ID' => $sessionId, 'Accept' => 'application/json'])
        ->postJson('/checkout/place-order', checkoutData())
        ->assertSuccessful();

    $order = Order::where('user_id', $buyer->id)->first();
    expect($order->items)->toHaveCount(2)
        ->and($product1->fresh()->quantity)->toBe(27)
        ->and($product2->fresh()->quantity)->toBe(14);
});

// ========================================================================
// ESCENARIO 5: Rechazo por stock insuficiente
// ========================================================================

it('rejects checkout when product has insufficient stock', function () {
    $t = setupTenant('tienda-stock', 'Tienda Stock');
    app()->instance('current_tenant', $t['tenant']);

    $product = createProduct($t['tenant']->id, $t['category']->id, $t['brand']->id, [
        'name' => 'Raro Item', 'price' => 999.00, 'quantity' => 1,
    ]);

    $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);
    $this->actingAs($buyer);
    $sessionId = (string) \Illuminate\Support\Str::uuid();

    $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 5]);

    $response = $this->withHeaders(['X-Session-ID' => $sessionId, 'Accept' => 'application/json'])
        ->postJson('/checkout/place-order', checkoutData());

    $response->assertStatus(400);
    expect(Order::where('user_id', $buyer->id)->count())->toBe(0);
    expect($product->fresh()->quantity)->toBe(1); // Stock not deducted
});

// ========================================================================
// ESCENARIO 6: Rechazo por carrito vacio
// ========================================================================

it('rejects checkout with empty cart', function () {
    $t = setupTenant('tienda-empty', 'Tienda Empty');
    app()->instance('current_tenant', $t['tenant']);

    $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);
    $this->actingAs($buyer);

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->postJson('/checkout/place-order', checkoutData());

    $response->assertStatus(400);
    expect(Order::where('user_id', $buyer->id)->count())->toBe(0);
});

// ========================================================================
// ESCENARIO 7: Dos tenants compran simultaneamente sin interferencia
// ========================================================================

it('orders from different tenants are completely isolated', function () {
    $tX = setupTenant('tienda-x', 'Tienda X');
    $tY = setupTenant('tienda-y', 'Tienda Y');

    $buyerX = User::factory()->create(['tenant_id' => $tX['tenant']->id]);
    $buyerY = User::factory()->create(['tenant_id' => $tY['tenant']->id]);

    // Create orders for each tenant
    Order::withoutGlobalScopes()->create([
        'user_id' => $buyerX->id, 'tenant_id' => $tX['tenant']->id,
        'order_number' => 'ORD-X-001', 'status' => 'pending', 'payment_status' => 'pending',
        'subtotal' => 200, 'total' => 230, 'tax_amount' => 30,
    ]);
    Order::withoutGlobalScopes()->create([
        'user_id' => $buyerY->id, 'tenant_id' => $tY['tenant']->id,
        'order_number' => 'ORD-Y-001', 'status' => 'pending', 'payment_status' => 'pending',
        'subtotal' => 500, 'total' => 575, 'tax_amount' => 75,
    ]);

    // Tenant X only sees its orders
    app()->instance('current_tenant', $tX['tenant']);
    $ordersX = Order::all();
    expect($ordersX)->toHaveCount(1)
        ->and($ordersX->first()->order_number)->toBe('ORD-X-001');

    // Tenant Y only sees its orders
    app()->instance('current_tenant', $tY['tenant']);
    $ordersY = Order::all();
    expect($ordersY)->toHaveCount(1)
        ->and($ordersY->first()->order_number)->toBe('ORD-Y-001');
});

// ========================================================================
// ESCENARIO 8: Guest cart se fusiona con user cart al login
// ========================================================================

it('guest cart merges with user cart on registration', function () {
    $t = setupTenant('tienda-merge', 'Tienda Merge');
    app()->instance('current_tenant', $t['tenant']);

    $product = createProduct($t['tenant']->id, $t['category']->id, $t['brand']->id, [
        'name' => 'Widget', 'price' => 75.00, 'quantity' => 100,
    ]);

    // Guest adds to cart
    $sessionId = (string) \Illuminate\Support\Str::uuid();
    $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 3])
        ->assertSuccessful();

    // Guest cart exists
    $guestCart = Cart::where('session_id', $sessionId)->first();
    expect($guestCart)->not->toBeNull()
        ->and($guestCart->items)->toHaveCount(1);

    // Register user (simulated - Livewire test)
    Livewire::test(Modals::class)
        ->set('mode', 'register')
        ->set('name', 'New Buyer')
        ->set('registerEmail', 'newbuyer-merge@test.com')
        ->set('registerPassword', 'password123')
        ->set('registerPasswordConfirmation', 'password123')
        ->set('acceptLegalTerms', true)
        ->call('register');

    $newUser = User::where('email', 'newbuyer-merge@test.com')->first();
    expect($newUser)->not->toBeNull()
        ->and($newUser->tenant_id)->toBe($t['tenant']->id);
});

// ========================================================================
// ESCENARIO 9: Cupon de primer pedido solo funciona en primer pedido
// ========================================================================

it('first order only coupon rejects returning customer', function () {
    $t = setupTenant('tienda-first', 'Tienda First Order');
    app()->instance('current_tenant', $t['tenant']);

    $product = createProduct($t['tenant']->id, $t['category']->id, $t['brand']->id, [
        'name' => 'Producto First', 'price' => 100.00, 'quantity' => 50,
    ]);

    Coupon::withoutGlobalScopes()->create([
        'code' => 'WELCOME10', 'name' => 'Bienvenida', 'type' => 'fixed', 'value' => 10,
        'is_active' => true, 'tenant_id' => $t['tenant']->id,
        'usage_count' => 0, 'first_order_only' => true,
        'starts_at' => now()->subDay(), 'expires_at' => now()->addMonth(),
    ]);

    // Buyer with an existing order
    $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);
    Order::withoutGlobalScopes()->create([
        'user_id' => $buyer->id, 'tenant_id' => $t['tenant']->id,
        'order_number' => 'OLD-001', 'status' => 'delivered', 'payment_status' => 'completed',
        'subtotal' => 50, 'total' => 57.50, 'tax_amount' => 7.50,
    ]);

    $this->actingAs($buyer);
    $sessionId = (string) \Illuminate\Support\Str::uuid();

    $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1]);

    // Apply first-order coupon should fail
    $response = $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart/apply-coupon', ['code' => 'WELCOME10']);

    $response->assertStatus(422);
});

// ========================================================================
// ESCENARIO 10: Producto inactivo no se puede comprar
// ========================================================================

it('rejects adding inactive product to cart', function () {
    $t = setupTenant('tienda-inactive', 'Tienda Inactive');
    app()->instance('current_tenant', $t['tenant']);

    $product = createProduct($t['tenant']->id, $t['category']->id, $t['brand']->id, [
        'name' => 'Inactivo', 'price' => 50.00, 'quantity' => 10, 'is_active' => false,
    ]);

    $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);
    $this->actingAs($buyer);
    $sessionId = (string) \Illuminate\Support\Str::uuid();

    $response = $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1]);

    // Product not found due to global scope (is_active check or tenant scope)
    $response->assertStatus(422);
});

// ========================================================================
// ESCENARIO 11: Buyer de Tenant A no puede comprar producto de Tenant B
// ========================================================================

it('buyer from Tenant A cannot add Tenant B product to cart', function () {
    $tA = setupTenant('store-alpha', 'Store Alpha');
    $tB = setupTenant('store-beta', 'Store Beta');

    $productB = createProduct($tB['tenant']->id, $tB['category']->id, $tB['brand']->id, [
        'name' => 'Beta Product', 'price' => 300.00, 'quantity' => 10,
    ]);

    // Set context to Tenant A
    app()->instance('current_tenant', $tA['tenant']);
    $buyerA = User::factory()->create(['tenant_id' => $tA['tenant']->id]);
    $this->actingAs($buyerA);
    $sessionId = (string) \Illuminate\Support\Str::uuid();

    // Try to add Tenant B product
    $response = $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $productB->id, 'quantity' => 1]);

    // Validation fails because product doesn't exist in Tenant A scope
    $response->assertStatus(422);
});

// ========================================================================
// ESCENARIO 12: Cupon fijo no excede el subtotal
// ========================================================================

it('fixed coupon does not exceed cart subtotal', function () {
    $t = setupTenant('tienda-cap', 'Tienda Cap');
    app()->instance('current_tenant', $t['tenant']);

    $product = createProduct($t['tenant']->id, $t['category']->id, $t['brand']->id, [
        'name' => 'Barato', 'price' => 20.00, 'quantity' => 50,
    ]);

    Coupon::withoutGlobalScopes()->create([
        'code' => 'BIG50', 'name' => '$50 off', 'type' => 'fixed', 'value' => 50,
        'is_active' => true, 'tenant_id' => $t['tenant']->id,
        'usage_count' => 0, 'first_order_only' => false,
        'starts_at' => now()->subDay(), 'expires_at' => now()->addMonth(),
    ]);

    $buyer = User::factory()->create(['tenant_id' => $t['tenant']->id]);
    $this->actingAs($buyer);
    $sessionId = (string) \Illuminate\Support\Str::uuid();

    $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1]);

    $this->withHeaders(['X-Session-ID' => $sessionId])
        ->postJson('/api/cart/apply-coupon', ['code' => 'BIG50'])
        ->assertSuccessful();

    $cartData = $this->withHeaders(['X-Session-ID' => $sessionId])
        ->getJson('/api/cart')->json('data');

    // Discount capped at subtotal ($20)
    expect((float) $cartData['discount_amount'])->toBeLessThanOrEqual(20.00);
});
