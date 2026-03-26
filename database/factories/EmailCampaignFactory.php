<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailCampaign>
 */
class EmailCampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Campaign '.$this->faker->words(3, true),
            'subject' => $this->faker->sentence,
            'content' => '<p>'.implode('</p><p>', $this->faker->paragraphs(3)).'</p>',
            'status' => 'draft',
            'segment' => $this->faker->randomElement(['all', 'customers', 'newsletter']),
            'recipients_count' => 0,
            'sent_count' => 0,
            'opened_count' => 0,
            'clicked_count' => 0,
            'bounced_count' => 0,
            'unsubscribed_count' => 0,
        ];
    }
}
