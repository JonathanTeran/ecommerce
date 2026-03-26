<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Brand '.Str::random(8);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => 'https://example.com/'.Str::slug($name),
            'description' => 'Description for '.$name,
            'position' => rand(0, 100),
            'is_active' => true,
            'is_featured' => rand(0, 100) < 20,
        ];
    }
}
