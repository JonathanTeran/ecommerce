<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Product '.Str::random(8);

        return [
            'brand_id' => Brand::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => strtoupper(Str::random(8)),
            'description' => 'Description for '.$name,
            'short_description' => 'Short description for '.$name,
            'price' => rand(10, 1000) + (rand(0, 99) / 100),
            'compare_price' => rand(0, 100) < 30 ? rand(1000, 2000) + (rand(0, 99) / 100) : null,
            'cost' => rand(5, 800) + (rand(0, 99) / 100),
            'quantity' => rand(0, 100),
            'low_stock_threshold' => 5,
            'weight' => rand(1, 100) / 10,
            'dimensions' => [
                'length' => rand(10, 100),
                'width' => rand(10, 100),
                'height' => rand(5, 50),
            ],
            'is_active' => true,
            'is_featured' => rand(0, 100) < 10,
            'is_new' => rand(0, 100) < 20,
            'requires_shipping' => true,
        ];
    }
}
