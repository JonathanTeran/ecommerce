<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReturn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnItem>
 */
class ReturnItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'return_id' => ProductReturn::factory(),
            'order_item_id' => OrderItem::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 3),
            'condition' => $this->faker->randomElement(['new', 'used', 'damaged']),
            'notes' => $this->faker->optional()->sentence,
        ];
    }
}
