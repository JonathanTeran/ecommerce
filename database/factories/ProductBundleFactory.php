<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductBundle>
 */
class ProductBundleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Bundle '.$this->faker->words(2, true);
        $price = $this->faker->randomFloat(2, 50, 500);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence,
            'price' => $price,
            'compare_price' => $this->faker->optional()->randomFloat(2, $price + 10, $price + 200),
            'is_active' => true,
        ];
    }
}
