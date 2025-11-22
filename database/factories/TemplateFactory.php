<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Template>
 */
class TemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(10),
            'thumbnail' => null,
            'content' => [
                [
                    'type' => 'hero',
                    'content' => [
                        'title' => 'Welcome to Your Landing Page',
                        'subtitle' => 'Create something amazing',
                        'buttonText' => 'Get Started',
                    ],
                ],
                [
                    'type' => 'features',
                    'content' => [
                        'title' => 'Features',
                        'items' => [
                            ['title' => 'Feature 1', 'description' => 'Description 1'],
                            ['title' => 'Feature 2', 'description' => 'Description 2'],
                            ['title' => 'Feature 3', 'description' => 'Description 3'],
                        ],
                    ],
                ],
            ],
            'category' => fake()->randomElement(['business', 'portfolio', 'landing', 'ecommerce']),
            'is_system' => false,
            'is_featured' => false,
            'is_published' => true,
        ];
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
