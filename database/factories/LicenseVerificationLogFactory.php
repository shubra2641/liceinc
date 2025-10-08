<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\LicenseVerificationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LicenseVerificationLog> */
class LicenseVerificationLogFactory extends Factory
{
    /**   * The name of the factory's corresponding model. *   * @var class-string<LicenseVerificationLog> */
    protected $model = LicenseVerificationLog::class;

    /**   * Define the model's default state. *   * @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'domain' => $this->faker->domainName(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'status' => 'success',
            'response' => json_encode(['status' => 'valid', 'message' => 'License verified successfully']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**   * Create a failed verification. */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'response' => json_encode(['status' => 'invalid', 'message' => 'License verification failed']),
        ]);
    }

    /**   * Create a blocked verification. */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'blocked',
            'response' => json_encode(['status' => 'blocked', 'message' => 'IP address blocked']),
        ]);
    }

    /**   * Set a specific domain. */
    public function domain(string $domain): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => $domain,
        ]);
    }

    /**   * Associate with a license. */
    public function forLicense(License $license): static
    {
        return $this->state(fn (array $attributes) => [
            'license_id' => $license->id,
        ]);
    }
}
