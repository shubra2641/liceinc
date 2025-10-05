<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Webhook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Webhook Factory.
 *
 * Factory for creating webhook test data with realistic values.
 */
class WebhookFactory extends Factory
{
    protected $model = Webhook::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'url' => $this->faker->url(),
            'secret' => Str::random(32),
            'events' => ['user.created', 'license.activated', 'invoice.paid'],
            'is_active' => $this->faker->boolean(80),
            'total_attempts' => $this->faker->numberBetween(0, 1000),
            'successful_attempts' => $this->faker->numberBetween(0, 800),
            'failed_attempts' => $this->faker->numberBetween(0, 200),
            'last_successful_at' => $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'last_failed_at' => $this->faker->optional(0.3)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the webhook is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the webhook is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the webhook has no secret.
     */
    public function withoutSecret(): static
    {
        return $this->state(fn (array $attributes) => [
            'secret' => null,
        ]);
    }

    /**
     * Indicate that the webhook has specific events.
     */
    public function withEvents(array $events): static
    {
        return $this->state(fn (array $attributes) => [
            'events' => $events,
        ]);
    }
}
