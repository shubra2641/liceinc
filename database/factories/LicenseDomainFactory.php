<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\LicenseDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LicenseDomain> */
class LicenseDomainFactory extends Factory
{
    /**   * The name of the factory's corresponding model. *   * @var class-string<LicenseDomain> */
    protected $model = LicenseDomain::class;

    /**   * Define the model's default state. *   * @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'domain' => $this->faker->domainName(),
            'verified' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**   * Create an unverified domain. */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified' => false,
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
