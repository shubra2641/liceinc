<?php

namespace Database\Factories;

use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
     * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebhookLog> */
class WebhookLogFactory extends Factory
{
    /**   * The name of the factory's corresponding model. *   * @var class-string<WebhookLog> */
    protected $model = WebhookLog::class;

    /**   * Define the model's default state. *   * @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'webhook_id' => \App\Models\Webhook::factory(),
            'event' => $this->faker->randomElement(['license.verified', 'license.failed', 'license.created']),
            'payload' => json_encode(['test' => 'data']),
            'response_status' => $this->faker->randomElement([200, 201, 400, 500]),
            'response_body' => $this->faker->text(),
            'attempts' => $this->faker->numberBetween(1, 5),
            'is_successful' => $this->faker->boolean(),
        ];
    }
}
