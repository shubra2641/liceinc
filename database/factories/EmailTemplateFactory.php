<?php

namespace Database\Factories;

use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<EmailTemplate>
     */
    protected $model = EmailTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'welcome', 'license_created', 'license_renewed', 'license_expired',
            'invoice_created', 'invoice_paid', 'ticket_created', 'ticket_replied',
            'password_reset', 'email_verification',
        ];

        return [
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement($types),
            'subject' => $this->faker->sentence(),
            'body' => $this->faker->paragraphs(3, true),
            'variables' => json_encode([
                'user_name' => 'User Name',
                'license_key' => 'License Key',
                'product_name' => 'Product Name',
                'expiry_date' => 'Expiry Date',
            ]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create an inactive template.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a specific template type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'name' => ucwords(str_replace('_', ' ', $type)) . ' Template',
        ]);
    }

    /**
     * Create a welcome email template.
     */
    public function welcome(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'welcome',
            'name' => 'Welcome Email Template',
            'subject' => 'Welcome to {{site_name}}!',
            'body' => 'Dear {{user_name}}, welcome to our platform!',
        ]);
    }

    /**
     * Create a license notification template.
     */
    public function licenseNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'license_created',
            'name' => 'License Created Template',
            'subject' => 'Your License for {{product_name}}',
            'body' => 'Your license key: {{license_key}}',
        ]);
    }
}
