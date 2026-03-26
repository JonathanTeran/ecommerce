<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('PROMO-####')),
            'name' => fake()->words(2, true),
            'type' => 'percentage',
            'value' => 10.00,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ];
    }

    public function fixed(float $value = 5.00): static
    {
        return $this->state(['type' => 'fixed', 'value' => $value]);
    }

    public function percentage(float $value = 10.00): static
    {
        return $this->state(['type' => 'percentage', 'value' => $value]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDay()]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function firstOrderOnly(): static
    {
        return $this->state(['first_order_only' => true]);
    }
}
