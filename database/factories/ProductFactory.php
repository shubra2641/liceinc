<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Product> */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug(),
            'envato_item_id' => $this->faker->optional()->numerify('#######'),
            'description' => $this->faker->paragraphs(2, true),
            'price' => $this->faker->randomFloat(2, 9.99, 99.99),
            'license_type' => 'regular',
            'support_days' => 180,
            'extended_support_price' => $this->faker->randomFloat(2, 5.99, 29.99),
            'extended_support_days' => 365,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Set a specific license type.
     */
    public function licenseType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'license_type' => $type,
        ]);
    }
}
