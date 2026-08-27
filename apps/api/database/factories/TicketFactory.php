<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'ticket_number' => 'TKT-'.fake()->unique()->numerify('##########'),
            'status' => TicketStatus::Valid,
            'issued_at' => now(),
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TicketStatus::Cancelled,
        ]);
    }
}
