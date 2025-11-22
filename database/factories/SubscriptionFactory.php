<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'stripe_subscription_id' => 'sub_' . $this->faker->unique()->regexify('[A-Za-z0-9]{14}'),
            'stripe_price_id' => 'price_' . $this->faker->regexify('[A-Za-z0-9]{14}'),
            'plan' => $this->faker->randomElement(['free', 'pro', 'business']),
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'cancel_at_period_end' => false,
        ];
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'cancel_at_period_end' => true,
        ]);
    }

    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'pro',
        ]);
    }

    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'business',
        ]);
    }
}
