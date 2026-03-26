<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
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
            'order_number' => 'ORD-'.strtoupper(Str::random(10)),
            'status' => $this->faker->randomElement(OrderStatus::cases()),
            'payment_status' => $this->faker->randomElement(PaymentStatus::cases()),
            'payment_method' => $this->faker->randomElement(PaymentMethod::cases()),
            'subtotal' => $this->faker->randomFloat(2, 50, 500),
            'tax_amount' => 0,
            'shipping_amount' => 10,
            'total' => 0, // Calculated in configure
            'currency' => 'USD',
            'notes' => $this->faker->optional()->sentence,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function ($order) {
            $order->tax_amount = $order->subtotal * 0.15;
            $order->total = $order->subtotal + $order->tax_amount + $order->shipping_amount;
        });
    }
}
