<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Webhook Log Factory.
 *
 * Factory for creating webhook log test data with realistic values.
 */
class WebhookLogFactory extends Factory
{
    protected $model = WebhookLog::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'webhook_id' => Webhook::factory(),
            'event_type' => $this->faker->randomElement([
                'user.created',
                'user.updated',
                'license.activated',
                'license.suspended',
                'invoice.paid',
                'invoice.overdue',
                'product.updated',
                'webhook.test',
            ]),
            'url' => $this->faker->url(),
            'payload' => [
                'id' => $this->faker->uuid(),
                'event' => $this->faker->word(),
                'timestamp' => $this->faker->unixTime(),
                'data' => [
                    'message' => $this->faker->sentence(),
                    'user_id' => $this->faker->numberBetween(1, 1000),
                ],
            ],
            'response_status' => $this->faker->randomElement([200, 201, 400, 401, 403, 404, 500, 502, 503]),
            'response_body' => $this->faker->optional(0.8)->randomElement([
                ['success' => true, 'message' => 'Webhook received'],
                ['error' => 'Invalid payload'],
                ['error' => 'Server error'],
                null,
            ]),
            'success' => $this->faker->boolean(75),
            'error_message' => $this->faker->optional(0.25)->sentence(),
            'attempt_number' => $this->faker->numberBetween(1, 5),
            'execution_time' => $this->faker->randomFloat(3, 0.1, 5.0),
        ];
    }

    /**
     * Indicate that the webhook log is successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => true,
            'response_status' => $this->faker->randomElement([200, 201]),
            'response_body' => ['success' => true, 'message' => 'Webhook received'],
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the webhook log failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => false,
            'response_status' => $this->faker->randomElement([400, 401, 403, 404, 500, 502, 503]),
            'response_body' => ['error' => $this->faker->randomElement(['Invalid payload', 'Server error', 'Timeout'])],
            'error_message' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the webhook log has specific event type.
     */
    public function withEventType(string $eventType): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => $eventType,
        ]);
    }

    /**
     * Indicate that the webhook log has specific webhook.
     */
    public function forWebhook(Webhook $webhook): static
    {
        return $this->state(fn (array $attributes) => [
            'webhook_id' => $webhook->id,
            'url' => $webhook->url,
        ]);
    }

    /**
     * Indicate that the webhook log is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-24 hours', 'now'),
        ]);
    }

    /**
     * Indicate that the webhook log is old.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-100 days', '-90 days'),
        ]);
    }
}
