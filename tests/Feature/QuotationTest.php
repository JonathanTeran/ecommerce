<?php

use App\Enums\Module;
use App\Enums\PlanType;
use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationResource;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createProductForQuotation(array $overrides = []): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'test-quot-cat'],
        ['name' => 'Test Category']
    );

    return Product::create(array_merge([
        'name' => 'Test Product',
        'slug' => 'test-product-' . uniqid(),
        'sku' => 'QT-' . strtoupper(uniqid()),
        'price' => 100,
        'cost' => 50,
        'quantity' => 20,
        'category_id' => $category->id,
    ], $overrides));
}

function createCartWithItems(User $user): Cart
{
    $cart = Cart::create(['user_id' => $user->id]);
    $product1 = createProductForQuotation(['name' => 'Product A', 'price' => 100]);
    $product2 = createProductForQuotation(['name' => 'Product B', 'price' => 200]);

    $cart->addItem($product1, 2);
    $cart->addItem($product2, 1);

    return $cart->fresh()->load('items.product');
}

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

    $this->tenant = Tenant::factory()->create(['is_default' => true]);
    app()->instance('current_tenant', $this->tenant);
});

it('creates quotation from cart', function () {
    $user = User::factory()->create();
    $cart = createCartWithItems($user);

    $service = new QuotationService;
    $quotation = $service->createFromCart($cart, $user, [
        'customer_name' => 'Juan Perez',
        'customer_email' => 'juan@test.com',
        'customer_phone' => '0991234567',
        'customer_company' => 'Test Corp',
        'customer_notes' => 'Necesito entrega urgente',
        'shipping_address' => ['name' => 'Juan', 'address' => 'Av. Principal', 'city' => 'Quito', 'state' => 'Pichincha', 'zip' => '170150', 'phone' => '0991234567'],
    ]);

    expect($quotation)->toBeInstanceOf(Quotation::class)
        ->and($quotation->status)->toBe(QuotationStatus::Pending)
        ->and($quotation->customer_name)->toBe('Juan Perez')
        ->and($quotation->customer_email)->toBe('juan@test.com')
        ->and($quotation->customer_company)->toBe('Test Corp')
        ->and($quotation->items)->toHaveCount(2)
        ->and($quotation->placed_at)->not->toBeNull();

    // Cart emptied after quotation creation
    expect($cart->fresh()->items)->toHaveCount(0);
});

it('generates quotation number automatically', function () {
    $user = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Draft,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
    ]);

    expect($quotation->quotation_number)->toStartWith('COT-' . now()->format('ymd') . '-');
});

it('calculates totals correctly', function () {
    $user = User::factory()->create();
    $cart = createCartWithItems($user);

    $service = new QuotationService;
    $quotation = $service->createFromCart($cart, $user, [
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'customer_phone' => '0991234567',
    ]);

    // 2*100 + 1*200 = 400 subtotal
    // Tax: 400 * 0.15 = 60
    // Total: 400 + 60 = 460
    expect((float) $quotation->subtotal)->toBe(400.00)
        ->and((float) $quotation->tax_amount)->toBe(60.00)
        ->and((float) $quotation->total)->toBe(460.00);
});

it('approves quotation', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
    ]);

    $service = new QuotationService;
    $service->approve($quotation, $admin);

    $quotation->refresh();
    expect($quotation->status)->toBe(QuotationStatus::Approved)
        ->and($quotation->approved_by)->toBe($admin->id)
        ->and($quotation->approved_at)->not->toBeNull();
});

it('rejects quotation with reason', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
    ]);

    $service = new QuotationService;
    $service->reject($quotation, $admin, 'Producto descontinuado');

    $quotation->refresh();
    expect($quotation->status)->toBe(QuotationStatus::Rejected)
        ->and($quotation->rejected_by)->toBe($admin->id)
        ->and($quotation->rejected_at)->not->toBeNull()
        ->and($quotation->rejection_reason)->toBe('Producto descontinuado');
});

it('converts approved quotation to order', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $product = createProductForQuotation(['quantity' => 50]);

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Approved,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'approved_by' => $admin->id,
        'approved_at' => now(),
        'valid_until' => now()->addDays(15),
    ]);

    $quotation->items()->create([
        'product_id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => 100,
        'quantity' => 1,
        'subtotal' => 100,
    ]);

    $this->actingAs($user);

    $service = new QuotationService;
    $order = $service->convertToOrder($quotation);

    $quotation->refresh();
    expect($quotation->status)->toBe(QuotationStatus::Converted)
        ->and($quotation->converted_order_id)->toBe($order->id)
        ->and($quotation->converted_at)->not->toBeNull()
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->name)->toBe($product->name);
});

it('cannot convert expired quotation', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Approved,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'approved_by' => $admin->id,
        'approved_at' => now(),
        'valid_until' => now()->subDay(),
    ]);

    expect($quotation->is_convertible)->toBeFalse();

    $service = new QuotationService;
    expect(fn () => $service->convertToOrder($quotation))
        ->toThrow(\RuntimeException::class);
});

it('cannot convert non-approved quotation', function () {
    $user = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
    ]);

    expect($quotation->is_convertible)->toBeFalse();

    $service = new QuotationService;
    expect(fn () => $service->convertToOrder($quotation))
        ->toThrow(\RuntimeException::class);
});

it('generates PDF', function () {
    $user = User::factory()->create();
    $product = createProductForQuotation();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Approved,
        'customer_name' => 'Test Client',
        'customer_email' => 'client@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'valid_until' => now()->addDays(15),
    ]);

    $quotation->items()->create([
        'product_id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => 100,
        'quantity' => 1,
        'subtotal' => 100,
    ]);

    $service = new QuotationService;
    $pdf = $service->generatePdf($quotation);

    expect($pdf)->toBeInstanceOf(\Barryvdh\DomPDF\PDF::class);
});

it('quotation resource is accessible with Quotations module', function () {
    $plan = Plan::create([
        'name' => 'Basic',
        'slug' => 'basic-quot-' . uniqid(),
        'type' => PlanType::Basic->value,
        'price' => 50,
        'modules' => array_map(fn (Module $m) => $m->value, PlanType::Basic->modules()),
    ]);

    $tenant = Tenant::create(['name' => 'Test Tenant', 'slug' => 'quot-tenant-' . uniqid()]);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $user->assignRole('admin');

    app()->instance('current_tenant', $tenant);
    $this->actingAs($user);

    expect($tenant->hasModule(Module::Quotations))->toBeTrue()
        ->and(QuotationResource::canAccess())->toBeTrue();
});

it('quotation resource is NOT accessible without Quotations module', function () {
    $plan = Plan::create([
        'name' => 'Custom No Quot',
        'slug' => 'no-quot-' . uniqid(),
        'type' => PlanType::Basic->value,
        'price' => 50,
        'modules' => ['products', 'categories'],
    ]);

    $tenant = Tenant::create(['name' => 'Test Tenant', 'slug' => 'no-quot-tenant-' . uniqid()]);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $user->assignRole('admin');

    app()->instance('current_tenant', $tenant);
    $this->actingAs($user);

    expect($tenant->hasModule(Module::Quotations))->toBeFalse()
        ->and(QuotationResource::canAccess())->toBeFalse();
});

it('super admin can access quotation resource', function () {
    $superAdmin = User::factory()->create(['tenant_id' => null]);
    $superAdmin->assignRole('super_admin');

    $this->actingAs($superAdmin);

    expect(QuotationResource::canAccess())->toBeTrue();
});

it('customer can request quotation via API', function () {
    $user = User::factory()->create();
    $cart = createCartWithItems($user);

    $this->actingAs($user);

    $response = $this->postJson('/api/quotation', [
        'customer_name' => 'Juan Perez',
        'customer_email' => 'juan@test.com',
        'customer_phone' => '0991234567',
        'shipping_address' => [
            'name' => 'Juan Perez',
            'address' => 'Av. Principal 123',
            'city' => 'Quito',
            'state' => 'Pichincha',
            'zip' => '170150',
            'phone' => '0991234567',
        ],
    ]);

    $response->assertSuccessful();
    expect($response->json('quotation_number'))->toStartWith('COT-');

    $this->assertDatabaseHas('quotations', [
        'customer_name' => 'Juan Perez',
        'customer_email' => 'juan@test.com',
        'user_id' => $user->id,
    ]);
});

it('guest cannot request quotation', function () {
    $response = $this->postJson('/api/quotation', [
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'customer_phone' => '0991234567',
        'shipping_address' => [
            'name' => 'Test',
            'address' => 'Test',
            'city' => 'Test',
            'state' => 'Test',
            'zip' => '12345',
            'phone' => '0991234567',
        ],
    ]);

    // Should get 401 (unauthorized) or validation error since no user
    expect($response->status())->toBeIn([401, 403]);
});

// ═══════════════════════════════════════════
// Tenant Isolation
// ═══════════════════════════════════════════

it('isolates quotations by tenant', function () {
    $tenantA = $this->tenant;
    $userA = User::factory()->create(['tenant_id' => $tenantA->id]);

    $quotationA = Quotation::create([
        'user_id' => $userA->id,
        'tenant_id' => $tenantA->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Tenant A Client',
        'customer_email' => 'a@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
    ]);

    $tenantB = Tenant::factory()->create();
    $userB = User::factory()->create(['tenant_id' => $tenantB->id]);

    app()->instance('current_tenant', $tenantB);

    $quotationB = Quotation::create([
        'user_id' => $userB->id,
        'tenant_id' => $tenantB->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Tenant B Client',
        'customer_email' => 'b@test.com',
        'subtotal' => 200,
        'tax_amount' => 30,
        'total' => 230,
    ]);

    // Tenant B should only see its own quotations
    expect(Quotation::count())->toBe(1)
        ->and(Quotation::first()->customer_name)->toBe('Tenant B Client');

    // Switch back to tenant A
    app()->instance('current_tenant', $tenantA);

    expect(Quotation::count())->toBe(1)
        ->and(Quotation::first()->customer_name)->toBe('Tenant A Client');
});

it('auto-assigns tenant_id on quotation creation', function () {
    $user = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
    ]);

    expect($quotation->tenant_id)->toBe($this->tenant->id);
});

it('my-quotations page only shows current tenant quotations', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'tenant_id' => $this->tenant->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
    ]);

    // Create quotation in another tenant
    $otherTenant = Tenant::factory()->create();
    Quotation::withoutGlobalScopes()->create([
        'user_id' => $user->id,
        'tenant_id' => $otherTenant->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'subtotal' => 500,
        'tax_amount' => 75,
        'total' => 575,
        'quotation_number' => 'COT-OTHER-123',
    ]);

    $this->actingAs($user)
        ->get(route('quotations.index'))
        ->assertSuccessful()
        ->assertSee($quotation->quotation_number)
        ->assertDontSee('COT-OTHER-123');
});

// ═══════════════════════════════════════════
// Status Transition Validation
// ═══════════════════════════════════════════

it('cannot approve an already approved quotation', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Approved,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'approved_by' => $admin->id,
        'approved_at' => now(),
    ]);

    $service = new QuotationService;
    expect(fn () => $service->approve($quotation, $admin))
        ->toThrow(\RuntimeException::class);
});

it('cannot reject an already rejected quotation', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Rejected,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'rejected_by' => $admin->id,
        'rejected_at' => now(),
        'rejection_reason' => 'No disponible',
    ]);

    $service = new QuotationService;
    expect(fn () => $service->reject($quotation, $admin, 'Otra razon'))
        ->toThrow(\RuntimeException::class);
});

it('cannot approve a converted quotation', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Converted,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'converted_at' => now(),
    ]);

    $service = new QuotationService;
    expect(fn () => $service->approve($quotation, $admin))
        ->toThrow(\RuntimeException::class);
});

// ═══════════════════════════════════════════
// Cart Clearing
// ═══════════════════════════════════════════

it('clears cart after quotation creation via API', function () {
    $user = User::factory()->create();
    $cart = createCartWithItems($user);

    expect($cart->items)->toHaveCount(2);

    $this->actingAs($user)
        ->postJson('/api/quotation', [
            'customer_name' => 'Test Cart Clear',
            'customer_email' => 'clear@test.com',
            'customer_phone' => '0991234567',
            'shipping_address' => [
                'name' => 'Test', 'address' => 'Test', 'city' => 'Test',
                'state' => 'Test', 'zip' => '12345', 'phone' => '0991234567',
            ],
        ])
        ->assertSuccessful();

    expect($cart->fresh()->items)->toHaveCount(0);
});

// ═══════════════════════════════════════════
// Stock Validation on Conversion
// ═══════════════════════════════════════════

it('cannot convert quotation when stock is insufficient', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $product = createProductForQuotation(['quantity' => 2]);

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Approved,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 500,
        'tax_amount' => 75,
        'total' => 575,
        'approved_by' => $admin->id,
        'approved_at' => now(),
        'valid_until' => now()->addDays(15),
    ]);

    $quotation->items()->create([
        'product_id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => 100,
        'quantity' => 5, // More than available stock (2)
        'subtotal' => 500,
    ]);

    $service = new QuotationService;
    expect(fn () => $service->convertToOrder($quotation))
        ->toThrow(\RuntimeException::class, 'Stock insuficiente');
});

it('deducts stock correctly on conversion', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $product = createProductForQuotation(['quantity' => 20]);

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Approved,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 300,
        'tax_amount' => 45,
        'total' => 345,
        'approved_by' => $admin->id,
        'approved_at' => now(),
        'valid_until' => now()->addDays(15),
    ]);

    $quotation->items()->create([
        'product_id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => 100,
        'quantity' => 3,
        'subtotal' => 300,
    ]);

    $this->actingAs($user);

    $service = new QuotationService;
    $order = $service->convertToOrder($quotation);

    expect($order)->not->toBeNull();
    expect($product->fresh()->quantity)->toBe(17); // 20 - 3
});

// ═══════════════════════════════════════════
// Valid Status Transitions (canTransitionTo)
// ═══════════════════════════════════════════

it('validates status transitions correctly', function () {
    expect(QuotationStatus::Pending->canTransitionTo(QuotationStatus::Approved))->toBeTrue()
        ->and(QuotationStatus::Pending->canTransitionTo(QuotationStatus::Rejected))->toBeTrue()
        ->and(QuotationStatus::Pending->canTransitionTo(QuotationStatus::Converted))->toBeFalse()
        ->and(QuotationStatus::Approved->canTransitionTo(QuotationStatus::Converted))->toBeTrue()
        ->and(QuotationStatus::Approved->canTransitionTo(QuotationStatus::Rejected))->toBeFalse()
        ->and(QuotationStatus::Rejected->canTransitionTo(QuotationStatus::Approved))->toBeFalse()
        ->and(QuotationStatus::Converted->canTransitionTo(QuotationStatus::Approved))->toBeFalse()
        ->and(QuotationStatus::Draft->canTransitionTo(QuotationStatus::Pending))->toBeTrue()
        ->and(QuotationStatus::Draft->canTransitionTo(QuotationStatus::Approved))->toBeFalse();
});

// ═══════════════════════════════════════════
// Visual Rendering Tests
// ═══════════════════════════════════════════

it('confirmation page shows customer notes and shipping address', function () {
    $user = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Test Visual',
        'customer_email' => 'visual@test.com',
        'customer_notes' => 'Entregar antes del mediodía',
        'shipping_address' => [
            'name' => 'Juan Perez',
            'address' => 'Av. Principal 123',
            'city' => 'Quito',
            'state' => 'Pichincha',
            'zip' => '170150',
            'phone' => '0991234567',
        ],
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
    ]);

    $quotation->items()->create([
        'product_id' => createProductForQuotation()->id,
        'name' => 'Widget Test',
        'sku' => 'WDG-001',
        'price' => 100,
        'quantity' => 1,
        'subtotal' => 100,
    ]);

    $this->actingAs($user)
        ->get(route('quotation.confirmation', $quotation))
        ->assertSuccessful()
        ->assertSee('Entregar antes del mediodía')
        ->assertSee('Av. Principal 123')
        ->assertSee('Quito')
        ->assertSee('Pichincha');
});

it('index page shows expired badge for expired quotations', function () {
    $user = User::factory()->create();

    Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Test Expired',
        'customer_email' => 'expired@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'valid_until' => now()->subDays(5),
    ]);

    $this->actingAs($user)
        ->get(route('quotations.index'))
        ->assertSuccessful()
        ->assertSee('Expirada');
});

it('index page shows expiring soon warning for quotations about to expire', function () {
    $user = User::factory()->create();

    Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Test Expiring',
        'customer_email' => 'expiring@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'valid_until' => now()->addDays(2),
    ]);

    $this->actingAs($user)
        ->get(route('quotations.index'))
        ->assertSuccessful()
        ->assertSee('Expira pronto');
});

it('index page shows all status badges correctly', function () {
    $user = User::factory()->create();

    foreach ([QuotationStatus::Pending, QuotationStatus::Approved, QuotationStatus::Rejected, QuotationStatus::Converted] as $status) {
        Quotation::create([
            'user_id' => $user->id,
            'status' => $status,
            'customer_name' => 'Test ' . $status->value,
            'customer_email' => $status->value . '@test.com',
            'subtotal' => 100,
            'tax_amount' => 15,
            'total' => 115,
        ]);
    }

    $response = $this->actingAs($user)->get(route('quotations.index'));

    $response->assertSuccessful();
    foreach ([QuotationStatus::Pending, QuotationStatus::Approved, QuotationStatus::Rejected, QuotationStatus::Converted] as $status) {
        $response->assertSee($status->getLabel());
    }
});

it('show page displays rejection reason', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Rejected,
        'customer_name' => 'Test Rejected',
        'customer_email' => 'rejected@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'rejected_by' => $admin->id,
        'rejected_at' => now(),
        'rejection_reason' => 'Producto no disponible actualmente',
    ]);

    $this->actingAs($user)
        ->get(route('quotations.show', $quotation))
        ->assertSuccessful()
        ->assertSee('Producto no disponible actualmente');
});

it('show page displays converted order link', function () {
    $user = User::factory()->create();

    $order = \App\Models\Order::create([
        'user_id' => $user->id,
        'status' => \App\Enums\OrderStatus::PENDING,
        'payment_status' => \App\Enums\PaymentStatus::PENDING,
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'placed_at' => now(),
    ]);

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Converted,
        'customer_name' => 'Test Converted',
        'customer_email' => 'converted@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'converted_order_id' => $order->id,
        'converted_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('quotations.show', $quotation))
        ->assertSuccessful()
        ->assertSee('Convertida');
});

it('confirmation page forbids access to other users quotation', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $owner->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Owner',
        'customer_email' => 'owner@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
    ]);

    $this->actingAs($intruder)
        ->get(route('quotation.confirmation', $quotation))
        ->assertForbidden();
});

it('rejected email view renders with items', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Rejected,
        'customer_name' => 'Test Mail',
        'customer_email' => 'mail@test.com',
        'subtotal' => 300,
        'tax_amount' => 45,
        'total' => 345,
        'rejected_by' => $admin->id,
        'rejected_at' => now(),
        'rejection_reason' => 'No hay stock suficiente',
    ]);

    $product = createProductForQuotation(['name' => 'Email Product']);
    $quotation->items()->create([
        'product_id' => $product->id,
        'name' => 'Email Product',
        'sku' => 'EP-001',
        'price' => 150,
        'quantity' => 2,
        'subtotal' => 300,
    ]);

    $mailable = new \App\Mail\QuotationRejectedMail($quotation);
    $rendered = $mailable->render();

    expect($rendered)
        ->toContain('Email Product')
        ->toContain('No hay stock suficiente')
        ->toContain('$300.00');
});
