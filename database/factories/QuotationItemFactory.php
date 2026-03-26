<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuotationItem>
 */
class QuotationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 10, 500);
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'quotation_id' => Quotation::factory(),
            'product_id' => Product::factory(),
            'name' => $this->faker->words(3, true),
            'sku' => 'QI-' . strtoupper($this->faker->bothify('???-####')),
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => round($price * $quantity, 2),
        ];
    }
}
