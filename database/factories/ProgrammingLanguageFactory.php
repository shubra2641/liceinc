<?php

namespace Database\Factories;

use App\Models\ProgrammingLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProgrammingLanguage> */
class ProgrammingLanguageFactory extends Factory
{
    /**   * The name of the factory's corresponding model. *   * @var class-string<ProgrammingLanguage> */
    protected $model = ProgrammingLanguage::class;

    /**   * Define the model's default state. *   * @return array<string, mixed> */
    public function definition(): array
    {
        $languages = [
            'PHP', 'JavaScript', 'Python', 'Java', 'C#', 'Ruby', 'Go', 'Swift',
            'Kotlin', 'TypeScript', 'C++', 'Rust', 'Dart', 'Scala', 'Elixir',
        ];

        $name = $this->faker->randomElement($languages);

        return [
            'name' => $name,
            'slug' => strtolower((string)$name),
            'description' => $this->faker->sentence(),
            'icon' => 'fab fa-' . strtolower((string)$name),
            'color' => $this->faker->hexColor(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**   * Create an inactive language. */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**   * Create a specific language. */
    public function language(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => strtolower($name),
            'icon' => 'fab fa-' . strtolower($name),
        ]);
    }

    /**   * Set a specific sort order. */
    public function sortOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
        ]);
    }
}
