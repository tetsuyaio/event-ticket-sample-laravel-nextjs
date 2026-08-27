<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory()->published(),
            'status' => ReservationStatus::Reserved,
            'reserved_at' => now(),
            'cancelled_at' => null,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ReservationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
