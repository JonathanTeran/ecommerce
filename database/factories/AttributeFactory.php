<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attribute>
 */
class AttributeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'type' => 'string',
            'options' => null,
            'is_filterable' => fake()->boolean(30),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function select(array $options = ['Rojo', 'Azul', 'Verde']): static
    {
        return $this->state(fn () => [
            'type' => 'select',
            'options' => $options,
        ]);
    }
}
