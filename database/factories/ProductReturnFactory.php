<?php

namespace Database\Factories;

use App\Enums\ReturnReason;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductReturn>
 */
class ProductReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'return_number' => 'RMA-'.strtoupper(Str::random(10)),
            'status' => 'requested',
            'reason' => $this->faker->randomElement(ReturnReason::cases()),
            'description' => $this->faker->sentence,
            'refund_amount' => $this->faker->optional()->randomFloat(2, 10, 500),
        ];
    }
}
