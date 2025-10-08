<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<License>
*/
    protected $model = License::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'license_key' => strtoupper($this->faker->bothify('????-????-????-????')),
            'purchase_code' => strtoupper($this->faker->bothify('????-????-????-????')),
            'license_type' => 'single',
            'status' => 'active',
            'license_expires_at' => now()->addYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the license is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_expires_at' => now()->subDay(),
            'status' => 'expired',
        ]);
    }

    /**
     * Indicate that the license is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'revoked',
        ]);
    }

    /**
     * Set a specific license type.
     */
    public function ofType(string $type): static
    {
        $maxDomains = match ($type) {
            'single' => 1,
            'multi' => 5,
            'developer' => 25,
            'extended' => 1,
            default => 1,
        };

        return $this->state(fn (array $attributes) => [
            'license_type' => $type,
            'max_domains' => $maxDomains,
            'max_usage' => $maxDomains,
        ]);
    }

    /**
     * Associate with a user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'customer_id' => $user->id,
        ]);
    }

    /**
     * Associate with a product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }
}
