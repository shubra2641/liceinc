<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketReply>
 */
class TicketReplyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TicketReply> */
    protected $model = TicketReply::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message' => $this->faker->paragraphs(3, true),
            'is_admin_reply' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create an admin reply.
     */
    public function adminReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin_reply' => true,
        ]);
    }

    /**
     * Create a customer reply.
     */
    public function customerReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin_reply' => false,
        ]);
    }

    /**
     * Set a short message.
     */
    public function shortMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'message' => $this->faker->sentence(),
        ]);
    }

    /**
     * Associate with a ticket.
     */
    public function forTicket(Ticket $ticket): static
    {
        return $this->state(fn (array $attributes) => [
            'ticket_id' => $ticket->id,
        ]);
    }

    /**
     * Associate with a user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
