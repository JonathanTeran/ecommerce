<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => [],
            'is_published' => true,
            'published_at' => now(),
            'sort_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function withRichTextBlock(): static
    {
        return $this->state(fn () => [
            'content' => [
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<p>Contenido de ejemplo para la pagina.</p>',
                    ],
                ],
            ],
        ]);
    }

    public function withHeroBlock(): static
    {
        return $this->state(fn () => [
            'content' => [
                [
                    'type' => 'hero_banner',
                    'data' => [
                        'heading' => 'Bienvenidos',
                        'subheading' => 'Descubre nuestros servicios',
                        'cta_text' => 'Ver Mas',
                        'cta_url' => '/shop',
                        'background_image' => null,
                    ],
                ],
            ],
        ]);
    }
}
