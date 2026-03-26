<?php

namespace Database\Factories;

use App\Enums\QuotationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 1000);
        $taxAmount = round($subtotal * 0.15, 2);

        return [
            'user_id' => User::factory(),
            'status' => QuotationStatus::Pending,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_company' => $this->faker->optional()->company(),
            'valid_until' => now()->addDays(15),
            'placed_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => QuotationStatus::Draft]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => QuotationStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => QuotationStatus::Rejected,
            'rejected_at' => now(),
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => QuotationStatus::Expired,
            'valid_until' => now()->subDays(5),
        ]);
    }
}
