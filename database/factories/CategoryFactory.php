<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Category '.Str::random(8);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Description for '.$name,
            'is_active' => true,
            'is_featured' => rand(0, 100) < 20,
            'position' => rand(0, 100),
            'icon' => 'heroicon-o-folder',
        ];
    }
}
