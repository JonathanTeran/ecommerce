<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Modals;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class CartPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_is_merged_on_login()
    {
        // 1. Create a guest session and add items to cart
        $sessionId = Session::getId();
        $product = Product::factory()->create();
        
        $cart = Cart::create([
            'session_id' => $sessionId,
            'user_id' => null,
            'currency' => 'USD',
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10.00,
            'subtotal' => 10.00,
        ]);

        // 2. Create User
        $user = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
        ]);

        // 3. Perform Login via Livewire Component
        Livewire::test(Modals::class)
            ->set('email', 'buyer@example.com')
            ->set('password', 'password')
            ->call('login');

        // 4. Assert Cart is now owned by user
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cart_is_merged_on_registration()
    {
        // 1. Create a guest session and add items to cart
        $sessionId = Session::getId();
        $product = Product::factory()->create();
        
        $cart = Cart::create([
            'session_id' => $sessionId,
            'user_id' => null,
            'currency' => 'USD',
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 20.00,
            'subtotal' => 40.00,
        ]);

        // 2. Perform Registration via Livewire Component
        Livewire::test(Modals::class)
            ->set('name', 'New Buyer')
            ->set('registerEmail', 'newbuyer@example.com')
            ->set('registerPassword', 'password')
            ->set('registerPasswordConfirmation', 'password')
            ->set('acceptLegalTerms', true)
            ->call('register');

        // 3. Get the new user
        $newUser = User::where('email', 'newbuyer@example.com')->first();

        // 4. Assert Cart is now owned by new user
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'user_id' => $newUser->id,
        ]);
    }
}
