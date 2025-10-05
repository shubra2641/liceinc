<?php

namespace Database\Factories;

use App\Models\PaymentSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentSetting>
 */
class PaymentSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gateways = ['stripe', 'paypal', 'razorpay', 'bank_transfer'];

        return [
            'gateway' => $this->faker->randomElement($gateways),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'settings' => json_encode([
                'api_key' => $this->faker->uuid(),
                'secret_key' => $this->faker->uuid(),
                'mode' => 'sandbox',
            ]),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create an inactive payment gateway.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a Stripe gateway.
     */
    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway' => 'stripe',
            'name' => 'Stripe',
            'description' => 'Pay with credit card via Stripe',
            'settings' => json_encode([
                'publishable_key' => 'pk_test_'.$this->faker->uuid(),
                'secret_key' => 'sk_test_'.$this->faker->uuid(),
                'mode' => 'sandbox',
            ]),
        ]);
    }

    /**
     * Create a PayPal gateway.
     */
    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway' => 'paypal',
            'name' => 'PayPal',
            'description' => 'Pay with PayPal account',
            'settings' => json_encode([
                'client_id' => $this->faker->uuid(),
                'client_secret' => $this->faker->uuid(),
                'mode' => 'sandbox',
            ]),
        ]);
    }

    /**
     * Set production mode.
     */
    public function production(): static
    {
        return $this->state(function (array $attributes) {
            $settings = json_decode($attributes['settings'], true);
            $settings['mode'] = 'live';

            return [
                'settings' => json_encode($settings),
            ];
        });
    }
}
