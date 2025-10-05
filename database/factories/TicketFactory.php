<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Test Ticket',
            'description' => 'Test ticket description',
            'status' => 'open',
            'priority' => 'medium',
            'department' => 'technical',
            'purchase_code' => 'TEST-'.strtoupper(\Illuminate\Support\Str::random(8)),
            'product_version' => '1.0.0',
            'browser_info' => 'Chrome/91.0',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the ticket is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }

    /**
     * Indicate that the ticket is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
        ]);
    }

    /**
     * Set a specific priority.
     */
    public function priority(string $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $priority,
        ]);
    }

    /**
     * Associate with a user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Associate with a category.
     */
    public function forCategory(TicketCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
            'department' => $category->department ?? 'technical',
        ]);
    }

    /**
     * Associate with a license.
     */
    public function forLicense(License $license): static
    {
        return $this->state(fn (array $attributes) => [
            'license_id' => $license->id,
            'purchase_code' => $license->license_key,
        ]);
    }
}
