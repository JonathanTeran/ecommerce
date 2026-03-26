<?php

namespace Database\Factories;

use App\Enums\SectionType;
use App\Models\HomepageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageSection>
 */
class HomepageSectionFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(SectionType::cases());

        return [
            'type' => $type,
            'name' => $type->label(),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
            'config' => $type->defaultConfig(),
        ];
    }

    public function hero(): static
    {
        return $this->state(fn () => [
            'type' => SectionType::Hero,
            'name' => 'Hero Principal',
        ]);
    }

    public function productGrid(): static
    {
        return $this->state(fn () => [
            'type' => SectionType::ProductGrid,
            'name' => 'Productos',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
