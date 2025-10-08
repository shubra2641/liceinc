<?php

namespace Database\Factories;

use App\Models\Webhook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Webhook>
 */
class WebhookFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Webhook>
     */
    protected $model = Webhook::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'url' => $this->faker->url(),
            'secret' => $this->faker->sha256(),
            'events' => json_encode(['license.verified', 'license.failed']),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
