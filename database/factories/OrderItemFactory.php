<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $this->faker->randomFloat(2, 1, 100);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'name' => $this->faker->words(3, true),
            'sku' => strtoupper($this->faker->bothify('SKU-####')),
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
            'options' => [],
        ];
    }
}
