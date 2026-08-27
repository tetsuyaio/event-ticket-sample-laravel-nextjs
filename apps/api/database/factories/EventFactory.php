<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(2, true),
            'venue' => fake()->city().' Hall',
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(2),
            'capacity' => 100,
            'reserved_count' => 0,
            'status' => EventStatus::Draft,
            'created_by' => User::factory()->admin(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => EventStatus::Published,
        ]);
    }

    public function soldOut(): static
    {
        return $this->state(fn (array $attributes): array => [
            'capacity' => 1,
            'reserved_count' => 1,
            'status' => EventStatus::Published,
        ]);
    }
}
