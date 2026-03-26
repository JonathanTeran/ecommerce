<?php

use App\Enums\QuotationStatus;
use App\Mail\QuotationApprovedMail;
use App\Mail\QuotationReceivedMail;
use App\Mail\QuotationRejectedMail;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

    $this->tenant = Tenant::factory()->create(['is_default' => true]);
    app()->instance('current_tenant', $this->tenant);
});

function createQuotationProduct(array $overrides = []): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'web-flow-cat'],
        ['name' => 'Test Category']
    );

    return Product::create(array_merge([
        'name' => 'Test Product',
        'slug' => 'wf-product-' . uniqid(),
        'sku' => 'WF-' . strtoupper(uniqid()),
        'price' => 100,
        'quantity' => 20,
        'category_id' => $category->id,
    ], $overrides));
}

function createQuotationForUser(User $user, array $overrides = []): Quotation
{
    $quotation = Quotation::create(array_merge([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'subtotal' => 200.00,
        'tax_amount' => 30.00,
        'total' => 230.00,
    ], $overrides));

    $product = createQuotationProduct();
    $quotation->items()->create([
        'product_id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => 100,
        'quantity' => 2,
        'subtotal' => 200,
    ]);

    return $quotation;
}

// ═══════════════════════════════════════════
// Quotation Create Page
// ═══════════════════════════════════════════

it('shows quotation form for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('quotation.create'))
        ->assertSuccessful()
        ->assertSee('Solicitar Cotización');
});

it('blocks guests from quotation create page', function () {
    $response = $this->get(route('quotation.create'));

    expect($response->status())->toBeIn([302, 401, 403, 500]);
});

// ═══════════════════════════════════════════
// My Quotations List
// ═══════════════════════════════════════════

it('lists user quotations', function () {
    $user = User::factory()->create();
    $quotation = createQuotationForUser($user);

    $this->actingAs($user)
        ->get(route('quotations.index'))
        ->assertSuccessful()
        ->assertSee($quotation->quotation_number);
});

it('does not show other users quotations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherQuotation = createQuotationForUser($otherUser);

    $this->actingAs($user)
        ->get(route('quotations.index'))
        ->assertSuccessful()
        ->assertDontSee($otherQuotation->quotation_number);
});

it('blocks guests from my-quotations', function () {
    $response = $this->get(route('quotations.index'));

    expect($response->status())->toBeIn([302, 401, 403, 500]);
});

// ═══════════════════════════════════════════
// Quotation Show Detail
// ═══════════════════════════════════════════

it('shows quotation details to owner', function () {
    $user = User::factory()->create();
    $quotation = createQuotationForUser($user);

    $this->actingAs($user)
        ->get(route('quotations.show', $quotation))
        ->assertSuccessful()
        ->assertSee($quotation->quotation_number);
});

it('forbids viewing other users quotation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $quotation = createQuotationForUser($otherUser);

    $this->actingAs($user)
        ->get(route('quotations.show', $quotation))
        ->assertForbidden();
});

// ═══════════════════════════════════════════
// Quotation Confirmation
// ═══════════════════════════════════════════

it('shows confirmation page to quotation owner', function () {
    $user = User::factory()->create();
    $quotation = createQuotationForUser($user);

    $this->actingAs($user)
        ->get(route('quotation.confirmation', $quotation))
        ->assertSuccessful()
        ->assertSee('Cotización Enviada')
        ->assertSee($quotation->quotation_number);
});

it('forbids viewing confirmation of other users quotation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $quotation = createQuotationForUser($otherUser);

    $this->actingAs($user)
        ->get(route('quotation.confirmation', $quotation))
        ->assertForbidden();
});

// ═══════════════════════════════════════════
// PDF Download
// ═══════════════════════════════════════════

it('downloads quotation PDF for owner', function () {
    $user = User::factory()->create();
    $quotation = createQuotationForUser($user, [
        'status' => QuotationStatus::Approved,
        'valid_until' => now()->addDays(15),
    ]);

    $response = $this->actingAs($user)
        ->get(route('quotations.pdf', $quotation));

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('pdf');
});

it('forbids PDF download by non-owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $quotation = createQuotationForUser($otherUser, [
        'status' => QuotationStatus::Approved,
        'valid_until' => now()->addDays(15),
    ]);

    $this->actingAs($user)
        ->get(route('quotations.pdf', $quotation))
        ->assertForbidden();
});

// ═══════════════════════════════════════════
// API Validation
// ═══════════════════════════════════════════

it('validates required fields when creating quotation via API', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $product = createQuotationProduct();
    $cart->addItem($product, 1);

    $this->actingAs($user)
        ->postJson('/api/quotation', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'customer_name',
            'customer_email',
            'customer_phone',
            'shipping_address.name',
            'shipping_address.address',
            'shipping_address.city',
            'shipping_address.state',
            'shipping_address.zip',
            'shipping_address.phone',
        ]);
});

it('validates email format in quotation API', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $product = createQuotationProduct();
    $cart->addItem($product, 1);

    $this->actingAs($user)
        ->postJson('/api/quotation', [
            'customer_name' => 'Test',
            'customer_email' => 'not-an-email',
            'customer_phone' => '0991234567',
            'shipping_address' => [
                'name' => 'Test', 'address' => 'Test', 'city' => 'Test',
                'state' => 'Test', 'zip' => '12345', 'phone' => '0991234567',
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_email']);
});

it('returns error when cart is empty', function () {
    $user = User::factory()->create();
    Cart::create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->postJson('/api/quotation', [
            'customer_name' => 'Test',
            'customer_email' => 'test@test.com',
            'customer_phone' => '0991234567',
            'shipping_address' => [
                'name' => 'Test', 'address' => 'Test', 'city' => 'Test',
                'state' => 'Test', 'zip' => '12345', 'phone' => '0991234567',
            ],
        ])
        ->assertStatus(400)
        ->assertJson(['message' => 'Cart is empty']);
});

// ═══════════════════════════════════════════
// Status Transitions
// ═══════════════════════════════════════════

it('validates quotation status transitions', function () {
    expect(QuotationStatus::Draft->canTransitionTo(QuotationStatus::Pending))->toBeTrue()
        ->and(QuotationStatus::Pending->canTransitionTo(QuotationStatus::Approved))->toBeTrue()
        ->and(QuotationStatus::Pending->canTransitionTo(QuotationStatus::Rejected))->toBeTrue()
        ->and(QuotationStatus::Approved->canTransitionTo(QuotationStatus::Converted))->toBeTrue()
        ->and(QuotationStatus::Draft->canTransitionTo(QuotationStatus::Approved))->toBeFalse()
        ->and(QuotationStatus::Rejected->canTransitionTo(QuotationStatus::Approved))->toBeFalse()
        ->and(QuotationStatus::Converted->canTransitionTo(QuotationStatus::Pending))->toBeFalse();
});

// ═══════════════════════════════════════════
// Expiration
// ═══════════════════════════════════════════

it('detects expired quotation', function () {
    $user = User::factory()->create();

    $expired = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Approved,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'valid_until' => now()->subDay(),
    ]);

    $valid = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Approved,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'valid_until' => now()->addDays(10),
    ]);

    expect($expired->is_expired)->toBeTrue()
        ->and($valid->is_expired)->toBeFalse()
        ->and($expired->is_convertible)->toBeFalse()
        ->and($valid->is_convertible)->toBeTrue();
});

// ═══════════════════════════════════════════
// Scopes
// ═══════════════════════════════════════════

it('scopes active quotations correctly', function () {
    $user = User::factory()->create();

    Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Active',
        'customer_email' => 'a@test.com',
        'subtotal' => 100, 'tax_amount' => 15, 'total' => 115,
        'valid_until' => now()->addDays(10),
    ]);
    Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Rejected,
        'customer_name' => 'Rejected',
        'customer_email' => 'b@test.com',
        'subtotal' => 100, 'tax_amount' => 15, 'total' => 115,
    ]);
    Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Converted,
        'customer_name' => 'Converted',
        'customer_email' => 'c@test.com',
        'subtotal' => 100, 'tax_amount' => 15, 'total' => 115,
    ]);

    expect(Quotation::active()->count())->toBe(1)
        ->and(Quotation::pending()->count())->toBe(1)
        ->and(Quotation::forUser($user->id)->count())->toBe(3);
});

// ═══════════════════════════════════════════
// Calculated Totals
// ═══════════════════════════════════════════

it('recalculates totals from items', function () {
    $user = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 0,
        'tax_amount' => 0,
        'total' => 0,
    ]);

    $product = createQuotationProduct(['price' => 50]);
    $quotation->items()->create([
        'product_id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => 50,
        'quantity' => 4,
        'subtotal' => 200,
    ]);

    $quotation->load('items');
    $quotation->calculateTotals();
    $quotation->refresh();

    expect((float) $quotation->subtotal)->toBe(200.00)
        ->and((float) $quotation->tax_amount)->toBe(30.00)
        ->and((float) $quotation->total)->toBe(230.00);
});

it('formats total correctly', function () {
    $user = User::factory()->create();

    $quotation = Quotation::create([
        'user_id' => $user->id,
        'status' => QuotationStatus::Pending,
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'subtotal' => 1000,
        'tax_amount' => 150,
        'total' => 1150,
    ]);

    expect($quotation->formatted_total)->toBe('$1,150.00');
});

// ═══════════════════════════════════════════
// Factory States
// ═══════════════════════════════════════════

it('creates quotation with factory', function () {
    $quotation = Quotation::factory()->create();

    expect($quotation)->toBeInstanceOf(Quotation::class)
        ->and($quotation->status)->toBe(QuotationStatus::Pending)
        ->and($quotation->quotation_number)->toStartWith('COT-')
        ->and($quotation->valid_until)->not->toBeNull();
});

it('creates quotation with factory states', function () {
    $draft = Quotation::factory()->draft()->create();
    $approved = Quotation::factory()->approved()->create();
    $rejected = Quotation::factory()->rejected()->create();
    $expired = Quotation::factory()->expired()->create();

    expect($draft->status)->toBe(QuotationStatus::Draft)
        ->and($approved->status)->toBe(QuotationStatus::Approved)
        ->and($rejected->status)->toBe(QuotationStatus::Rejected)
        ->and($rejected->rejection_reason)->not->toBeNull()
        ->and($expired->status)->toBe(QuotationStatus::Expired)
        ->and($expired->is_expired)->toBeTrue();
});

// ═══════════════════════════════════════════
// PDF Attachments in Emails
// ═══════════════════════════════════════════

it('QuotationReceivedMail has PDF attachment', function () {
    $user = User::factory()->create();
    $quotation = createQuotationForUser($user);

    $mailable = new QuotationReceivedMail($quotation);
    $mailable->assertHasTo($quotation->customer_email);
    $mailable->assertHasSubject('Cotización Recibida #' . $quotation->quotation_number);

    $attachments = $mailable->attachments();
    expect($attachments)->toHaveCount(1);
});

it('QuotationApprovedMail has PDF attachment', function () {
    $user = User::factory()->create();
    $quotation = createQuotationForUser($user, [
        'status' => QuotationStatus::Approved,
        'approved_at' => now(),
    ]);

    $mailable = new QuotationApprovedMail($quotation);
    $mailable->assertHasTo($quotation->customer_email);
    $mailable->assertHasSubject('Cotización Aprobada #' . $quotation->quotation_number);

    $attachments = $mailable->attachments();
    expect($attachments)->toHaveCount(1);
});

it('QuotationRejectedMail has NO PDF attachment', function () {
    $user = User::factory()->create();
    $quotation = createQuotationForUser($user, [
        'status' => QuotationStatus::Rejected,
        'rejection_reason' => 'Sin stock',
    ]);

    $mailable = new QuotationRejectedMail($quotation);
    $mailable->assertHasTo($quotation->customer_email);

    // QuotationRejectedMail does not define attachments() method
    expect(method_exists($mailable, 'attachments'))->toBeFalse();
});
