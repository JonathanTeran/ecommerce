<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductBundle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductBundleItem>
 */
class ProductBundleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_bundle_id' => ProductBundle::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 3),
        ];
    }
}
