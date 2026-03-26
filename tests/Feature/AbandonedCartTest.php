<?php

use App\Mail\AbandonedCartReminderMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
});

function createProductForCart(array $overrides = []): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'cart-test-cat'],
        ['name' => 'Test Category']
    );

    return Product::create(array_merge([
        'name' => 'Cart Product',
        'slug' => 'cart-prod-' . uniqid(),
        'sku' => 'CP-' . strtoupper(uniqid()),
        'price' => 50,
        'quantity' => 100,
        'category_id' => $category->id,
        'is_active' => true,
    ], $overrides));
}

function createAbandonedCart(User $user, int $hoursAgo = 3): Cart
{
    $cart = Cart::create([
        'user_id' => $user->id,
        'expires_at' => now()->addDays(30),
    ]);

    $product = createProductForCart();
    $cart->addItem($product, 2);

    // Backdate updated_at to simulate abandonment using DB to bypass timestamps
    DB::table('carts')->where('id', $cart->id)->update([
        'updated_at' => now()->subHours($hoursAgo),
    ]);

    return $cart->fresh();
}

it('sends reminder for abandoned carts', function () {
    Mail::fake();

    $user = User::factory()->create();
    $cart = createAbandonedCart($user, 3);

    $this->artisan('carts:send-abandoned-reminders', ['--hours' => 2])
        ->assertSuccessful();

    $cart->refresh();
    expect($cart->reminder_count)->toBe(1)
        ->and($cart->reminder_sent_at)->not->toBeNull();
});

it('does not send reminder for recent carts', function () {
    Mail::fake();

    $user = User::factory()->create();
    $cart = Cart::create([
        'user_id' => $user->id,
        'expires_at' => now()->addDays(30),
    ]);
    $product = createProductForCart();
    $cart->addItem($product, 1);

    $this->artisan('carts:send-abandoned-reminders', ['--hours' => 2])
        ->assertSuccessful();

    $cart->refresh();
    expect($cart->reminder_count)->toBe(0);
});

it('respects max reminders limit', function () {
    Mail::fake();

    $user = User::factory()->create();
    $cart = createAbandonedCart($user, 3);

    // Already sent max reminders
    DB::table('carts')->where('id', $cart->id)->update([
        'reminder_count' => 2,
        'reminder_sent_at' => now()->subDays(2),
    ]);

    $this->artisan('carts:send-abandoned-reminders', ['--hours' => 2, '--max-reminders' => 2])
        ->assertSuccessful();

    $cart->refresh();
    expect($cart->reminder_count)->toBe(2);
});

it('does not send reminder for empty carts', function () {
    Mail::fake();

    $user = User::factory()->create();
    $cart = Cart::create([
        'user_id' => $user->id,
        'expires_at' => now()->addDays(30),
    ]);

    DB::table('carts')->where('id', $cart->id)->update(['updated_at' => now()->subHours(5)]);

    $this->artisan('carts:send-abandoned-reminders', ['--hours' => 2])
        ->assertSuccessful();

    $cart->refresh();
    expect($cart->reminder_count)->toBe(0);
});

it('does not send reminder for guest carts', function () {
    Mail::fake();

    $cart = Cart::create([
        'user_id' => null,
        'expires_at' => now()->addDays(30),
    ]);
    $product = createProductForCart();
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => $product->price,
    ]);

    DB::table('carts')->where('id', $cart->id)->update(['updated_at' => now()->subHours(5)]);

    $this->artisan('carts:send-abandoned-reminders', ['--hours' => 2])
        ->assertSuccessful();

    $cart->refresh();
    expect($cart->reminder_count)->toBe(0);
});

it('waits 24h between reminders', function () {
    Mail::fake();

    $user = User::factory()->create();
    $cart = createAbandonedCart($user, 5);

    // First reminder was sent 12 hours ago (less than 24h)
    DB::table('carts')->where('id', $cart->id)->update([
        'reminder_count' => 1,
        'reminder_sent_at' => now()->subHours(12),
    ]);

    $this->artisan('carts:send-abandoned-reminders', ['--hours' => 2, '--max-reminders' => 3])
        ->assertSuccessful();

    $cart->refresh();
    expect($cart->reminder_count)->toBe(1); // Not incremented
});

it('builds AbandonedCartReminderMail correctly', function () {
    $user = User::factory()->create();
    $cart = createAbandonedCart($user);

    $mailable = new AbandonedCartReminderMail($cart);

    $mailable->assertHasTo($user->email);
    $mailable->assertHasSubject('Tienes productos esperando en tu carrito');
});
