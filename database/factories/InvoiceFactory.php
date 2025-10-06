<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\License;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-'.str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'total_amount' => 29.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
            'invoice_date' => now(),
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'notes' => 'Test invoice',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'payment_status' => 'completed',
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => now()->subDays(5),
        ]);
    }

    /**
     * Indicate that the invoice is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'payment_status' => 'cancelled',
        ]);
    }

    /**
     * Set a specific amount.
     */
    public function amount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $amount,
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
     * Associate with a license.
     */
    public function forLicense(License $license): static
    {
        return $this->state(fn (array $attributes) => [
            'license_id' => $license->id,
        ]);
    }
}
