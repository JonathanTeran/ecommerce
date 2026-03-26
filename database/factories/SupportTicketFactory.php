<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ticket_number' => 'TKT-'.strtoupper(Str::random(10)),
            'subject' => $this->faker->sentence,
            'status' => 'open',
            'priority' => 'medium',
            'category' => $this->faker->randomElement(['general', 'order', 'product', 'payment', 'shipping']),
        ];
    }
}
