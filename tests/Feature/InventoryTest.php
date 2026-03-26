<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_service_add_stock()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = \App\Models\Category::create(['name' => 'Test Category', 'slug' => 'test-cat']);
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'price' => 100,
            'category_id' => $category->id,
            'quantity' => 0,
        ]);

        $service = new InventoryService;
        $movement = $service->addStock($product, 10, 50.00, 'purchase', null, $user->id);

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $movement->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'balance_quantity' => 10,
            'type' => 'purchase',
        ]);

        $this->assertEquals(10, $product->fresh()->quantity);
        $this->assertEquals(50.00, $product->fresh()->cost);
    }

    public function test_inventory_service_remove_stock()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = \App\Models\Category::create(['name' => 'Test Category', 'slug' => 'test-cat']);
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'price' => 100,
            'cost' => 50,
            'category_id' => $category->id,
            'quantity' => 20,
        ]);

        $service = new InventoryService;
        $movement = $service->removeStock($product, 5, 'sale', null, $user->id);

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $movement->id,
            'product_id' => $product->id,
            'quantity' => -5,
            'balance_quantity' => 15,
            'type' => 'sale',
        ]);

        $this->assertEquals(15, $product->fresh()->quantity);
    }
}
