<?php

namespace Database\Factories;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KbArticle> */
class KbArticleFactory extends Factory
{
    /**   * The name of the factory's corresponding model. *   * @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected $model = KbArticle::class;

    /**   * Define the model's default state. *   * @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'slug' => $this->faker->slug(),
            'content' => $this->faker->paragraphs(5, true),
            'excerpt' => $this->faker->sentence(),
            'meta_title' => $this->faker->sentence(6),
            'meta_description' => $this->faker->sentence(10),
            'featured_image' => 'images/kb/' . $this->faker->uuid() . '.jpg',
            'status' => 'published',
            'views' => $this->faker->numberBetween(0, 1000),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**   * Create a draft article. */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**   * Create an unpublished article. */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unpublished',
        ]);
    }

    /**   * Create a featured article. */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**   * Create a popular article. */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views' => $this->faker->numberBetween(5000, 50000),
        ]);
    }

    /**   * Associate with a category. */
    public function inCategory(KbCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'kb_category_id' => $category->id,
        ]);
    }
}
