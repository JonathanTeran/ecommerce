<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoyaltyProgram>
 */
class LoyaltyProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Programa de Puntos',
            'points_per_dollar' => 1.00,
            'redemption_rate' => 0.01,
            'minimum_redemption_points' => 100,
            'is_active' => true,
        ];
    }
}
