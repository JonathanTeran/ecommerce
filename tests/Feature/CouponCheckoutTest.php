<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCouponTestProduct(array $overrides = []): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'coupon-test-cat'],
        ['name' => 'Test Category']
    );

    return Product::create(array_merge([
        'name' => 'Coupon Product',
        'slug' => 'coupon-product-' . uniqid(),
        'sku' => 'CP-' . strtoupper(uniqid()),
        'price' => 100,
        'cost' => 50,
        'quantity' => 20,
        'category_id' => $category->id,
    ], $overrides));
}

function createCouponTestCart(User $user): Cart
{
    $cart = Cart::create(['user_id' => $user->id]);
    $product = createCouponTestProduct(['price' => 200]);
    $cart->addItem($product, 2);

    return $cart->fresh()->load('items.product');
}

// --- Coupon Validation Tests ---

it('validates percentage coupon calculates correctly', function () {
    $coupon = Coupon::factory()->percentage(15)->create();

    $discount = $coupon->calculateDiscount(400);

    expect($discount)->toBe(60.0);
});

it('validates fixed coupon calculates correctly', function () {
    $coupon = Coupon::factory()->fixed(25)->create();

    $discount = $coupon->calculateDiscount(400);

    expect($discount)->toBe(25.0);
});

it('fixed coupon does not exceed subtotal', function () {
    $coupon = Coupon::factory()->fixed(500)->create();

    $discount = $coupon->calculateDiscount(100);

    expect($discount)->toBe(100.0);
});

it('percentage coupon respects max discount amount', function () {
    $coupon = Coupon::factory()->percentage(50)->create([
        'max_discount_amount' => 30,
    ]);

    $discount = $coupon->calculateDiscount(200);

    expect($discount)->toBe(30.0);
});

it('rejects inactive coupon', function () {
    $coupon = Coupon::factory()->inactive()->create();

    expect($coupon->isValidFor(100))->toBeFalse();
});

it('rejects expired coupon', function () {
    $coupon = Coupon::factory()->expired()->create();

    expect($coupon->isValidFor(100))->toBeFalse();
});

it('rejects coupon below minimum order amount', function () {
    $coupon = Coupon::factory()->create(['min_order_amount' => 500]);

    expect($coupon->isValidFor(200))->toBeFalse();
});

it('rejects coupon that exceeded usage limit', function () {
    $coupon = Coupon::factory()->create([
        'usage_limit' => 5,
        'usage_count' => 5,
    ]);

    expect($coupon->isValidFor(100))->toBeFalse();
});

it('rejects coupon per user limit exceeded', function () {
    $user = User::factory()->create();
    $coupon = Coupon::factory()->create([
        'usage_limit_per_user' => 1,
    ]);

    // Simulate a previous order with this coupon
    Order::withoutGlobalScopes()->create([
        'user_id' => $user->id,
        'coupon_code' => $coupon->code,
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => 100,
        'tax_amount' => 15,
        'total' => 115,
        'placed_at' => now(),
    ]);

    expect($coupon->isValidFor(100, $user->id))->toBeFalse();
});

it('rejects first order only coupon for returning customer', function () {
    $user = User::factory()->create();
    $coupon = Coupon::factory()->firstOrderOnly()->create();

    Order::withoutGlobalScopes()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => 50,
        'tax_amount' => 7.5,
        'total' => 57.5,
        'placed_at' => now(),
    ]);

    expect($coupon->isValidFor(100, $user->id))->toBeFalse();
});

it('accepts first order only coupon for new customer', function () {
    $user = User::factory()->create();
    $coupon = Coupon::factory()->firstOrderOnly()->create();

    expect($coupon->isValidFor(100, $user->id))->toBeTrue();
});

// --- Cart Coupon Integration ---

it('applies coupon to cart', function () {
    $user = User::factory()->create();
    $cart = createCouponTestCart($user);
    $coupon = Coupon::factory()->percentage(10)->create();

    $result = $cart->applyCoupon($coupon);

    expect($result)->toBeTrue()
        ->and($cart->fresh()->coupon_id)->toBe($coupon->id);
});

it('removes coupon from cart', function () {
    $user = User::factory()->create();
    $cart = createCouponTestCart($user);
    $coupon = Coupon::factory()->create();
    $cart->applyCoupon($coupon);

    $cart->removeCoupon();

    expect($cart->fresh()->coupon_id)->toBeNull();
});

it('cart discount amount reflects coupon', function () {
    $user = User::factory()->create();
    $cart = createCouponTestCart($user); // 2 x $200 = $400 subtotal
    $coupon = Coupon::factory()->percentage(10)->create();
    $cart->applyCoupon($coupon);
    $cart = $cart->fresh()->load('coupon', 'items.product');

    // 10% of 400 = 40
    expect($cart->discount_amount)->toBe(40.0)
        ->and($cart->subtotal)->toBe(400.0);
});

it('cart total includes discount', function () {
    $user = User::factory()->create();
    $cart = createCouponTestCart($user); // subtotal = 400
    $coupon = Coupon::factory()->fixed(50)->create();
    $cart->applyCoupon($coupon);
    $cart = $cart->fresh()->load('coupon', 'items.product');

    // subtotal 400 - discount 50 = 350 taxable
    // tax: 350 * 0.15 = 52.5
    // total: 350 + 52.5 = 402.5
    expect($cart->discount_amount)->toBe(50.0)
        ->and($cart->total)->toBe(402.5);
});

// --- API Endpoints ---

it('applies coupon via API', function () {
    $user = User::factory()->create();
    $cart = createCouponTestCart($user);
    $coupon = Coupon::factory()->create(['code' => 'TESTCODE']);

    $response = $this->actingAs($user)->postJson('/api/cart/apply-coupon', [
        'code' => 'TESTCODE',
    ]);

    $response->assertSuccessful();
    expect($response->json('data.coupon_code'))->toBe('TESTCODE');
});

it('returns error for invalid coupon code', function () {
    $user = User::factory()->create();
    createCouponTestCart($user);

    $response = $this->actingAs($user)->postJson('/api/cart/apply-coupon', [
        'code' => 'NONEXISTENT',
    ]);

    $response->assertNotFound();
});

it('removes coupon via API', function () {
    $user = User::factory()->create();
    $cart = createCouponTestCart($user);
    $coupon = Coupon::factory()->create();
    $cart->applyCoupon($coupon);

    $response = $this->actingAs($user)->deleteJson('/api/cart/coupon');

    $response->assertSuccessful();
    expect($response->json('data.coupon_code'))->toBeNull();
});

it('checkout data includes coupon info', function () {
    $user = User::factory()->create();
    $cart = createCouponTestCart($user);
    $coupon = Coupon::factory()->percentage(10)->create(['code' => 'SAVE10']);
    $cart->applyCoupon($coupon);
    $cart = $cart->fresh()->load('coupon', 'items.product');

    $data = $cart->toCheckoutData();

    expect($data['coupon_code'])->toBe('SAVE10')
        ->and($data['discount_amount'])->toBe(40.0);
});
