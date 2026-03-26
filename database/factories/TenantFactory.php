<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(2),
            'is_active' => true,
        ];
    }

    public function deactivated(int $monthsAgo = 4): static
    {
        return $this->state(fn () => [
            'is_active' => false,
            'deactivated_at' => now()->subMonths($monthsAgo),
        ]);
    }
}
