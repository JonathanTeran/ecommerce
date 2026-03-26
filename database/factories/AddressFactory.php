<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['shipping', 'billing']),
            'label' => fake()->randomElement(['Casa', 'Oficina']),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address_line_1' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => 'Pichincha',
            'country' => 'Ecuador',
            'phone' => fake()->phoneNumber(),
            'is_default' => false,
        ];
    }
}
