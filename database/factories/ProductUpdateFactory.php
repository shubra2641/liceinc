<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductUpdate>
 */
class ProductUpdateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected $model = ProductUpdate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'version' => '1.1.0',
            'title' => 'Test Update',
            'description' => 'Test update description',
            'changelog' => [
                'added' => ['New feature 1', 'New feature 2'],
                'fixed' => ['Bug fix 1', 'Bug fix 2'],
                'changed' => ['Improvement 1'],
            ],
            'file_path' => 'updates/test-update-v1.1.0.zip',
            'file_name' => 'test-update-v1.1.0.zip',
            'file_size' => 1024000, // 1MB
            'file_hash' => md5('test-checksum'),
            'is_major' => false,
            'is_required' => false,
            'is_active' => true,
            'requirements' => [
                'php' => '>=8.0',
                'laravel' => '>=9.0',
            ],
            'compatibility' => [
                'min_version' => '1.0.0',
                'max_version' => null,
            ],
            'released_at' => now(),
        ];
    }

    /**
     * Indicate that the update is critical.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_critical' => true,
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the update is a security update.
     */
    public function security(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_security' => true,
            'is_critical' => true,
            'priority' => 'urgent',
        ]);
    }

    /**
     * Indicate that the update is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'released_at' => now(),
        ]);
    }

    /**
     * Set a specific version.
     */
    public function version(string $version): static
    {
        return $this->state(fn (array $attributes) => [
            'version' => $version,
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
