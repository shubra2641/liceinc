<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductFile>
 */
class ProductFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductFile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalName = $this->faker->words(3, true).'.zip';

        return [
            'original_name' => $originalName,
            'encrypted_name' => $this->faker->uuid().'.encrypted',
            'file_path' => 'uploads/products/'.$this->faker->uuid().'.encrypted',
            'file_type' => 'application/zip',
            'file_size' => $this->faker->numberBetween(1000000, 50000000), // 1MB to 50MB
            'encryption_key' => 'encrypted_key_'.$this->faker->uuid(),
            'checksum' => $this->faker->sha256(),
            'description' => $this->faker->sentence(),
            'download_count' => $this->faker->numberBetween(0, 1000),
            'is_active' => true,
        ];
    }

    /**
     * Create an inactive file.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
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
     * Set high download count.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'download_count' => $this->faker->numberBetween(5000, 50000),
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
