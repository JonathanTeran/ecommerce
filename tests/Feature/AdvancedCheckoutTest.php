<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup common data
        $this->category = Category::factory()->create();
        $this->brand = Brand::factory()->create();

        GeneralSetting::create([
            'payment_gateways_config' => [
                'bank_transfer_enabled' => true,
                'bank_transfer_requires_proof' => false,
                'bank_transfer_surcharge_percentage' => 0,
            ],
        ]);

        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'price' => 100.00,
            'quantity' => 5, // Limited stock
            'is_active' => true,
        ]);
    }

    protected function validCheckoutData(array $overrides = []): array
    {
        return array_merge([
            'payment_method' => 'bank_transfer',
            'shipping_address' => [
                'name' => 'Test User',
                'identity_document' => '1712345678',
                'email' => 'test@example.com',
                'address' => 'Av. Test 123',
                'city' => 'Quito',
                'state' => 'Pichincha',
                'zip' => '170150',
                'phone' => '0991234567',
            ],
            'billing_address' => [
                'name' => 'Test User',
                'tax_id' => '1712345678001',
                'address' => 'Av. Test 123',
                'city' => 'Quito',
                'state' => 'Pichincha',
                'zip' => '170150',
                'phone' => '0991234567',
            ],
            'accepted_legal_documents' => true,
        ], $overrides);
    }

    public function test_cannot_purchase_out_of_stock_items()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create cart with quantity > stock
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->addItem($this->product, 10); // Request 10, have 5

        $response = $this->postJson('/checkout/place-order', $this->validCheckoutData());

        // Expect failure due to insufficient stock
        $response->assertStatus(400);
        $response->assertStatus(400); // Message now in Spanish with product name
    }

    public function test_cannot_purchase_inactive_product()
    {
        $this->product->update(['is_active' => false]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = Cart::create(['user_id' => $user->id]);
        $cart->addItem($this->product, 1);

        $response = $this->postJson('/checkout/place-order', $this->validCheckoutData());

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'Uno o mas productos no estan disponibles.']);
    }

    public function test_order_deducts_stock_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = Cart::create(['user_id' => $user->id]);
        $cart->addItem($this->product, 2);

        $response = $this->postJson('/checkout/place-order', $this->validCheckoutData());

        $response->assertSuccessful();

        // Verify stock reduced
        $this->product->refresh();
        $this->assertEquals(3, $this->product->quantity); // 5 - 2 = 3
    }
}
