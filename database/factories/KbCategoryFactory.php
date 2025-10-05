<?php

namespace Database\Factories;

use App\Models\KbCategory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KbCategory>
 */
class KbCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = KbCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Test KB Category',
            'description' => 'Test knowledge base category description',
            'slug' => 'test-kb-category',
            'is_published' => true,
            'requires_serial' => false,
            'serial' => null,
            'sort_order' => 1,
            'meta_title' => 'Test KB Category',
            'meta_description' => 'Test knowledge base category description',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the category is not published.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    /**
     * Indicate that the category requires serial.
     */
    public function requiresSerial(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_serial' => true,
            'serial' => 'TEST-SERIAL-123',
        ]);
    }

    /**
     * Set a specific sort order.
     */
    public function sortOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
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
