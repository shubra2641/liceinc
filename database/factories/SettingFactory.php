<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'test_setting_'.rand(1000, 9999),
            'value' => 'test_value',
            'type' => 'general',
            'site_name' => 'Test Site',
            'site_logo' => null,
            'support_email' => 'test@example.com',
            'site_description' => 'Test description',
            'envato_personal_token' => null,
            'envato_api_key' => null,
            'envato_auth_enabled' => false,
            'envato_username' => null,
            'auto_generate_license' => true,
            'default_license_length' => 32,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the setting is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the setting is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Set the setting type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
