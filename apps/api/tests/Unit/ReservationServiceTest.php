<?php

use App\Enums\ReservationStatus;
use App\Exceptions\ApiException;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ReservationService;

it('rolls back all reservation writes when the event is sold out', function (): void {
    $user = User::factory()->create();
    $event = Event::factory()->soldOut()->create();
    $service = app(ReservationService::class);

    expect(fn () => $service->reserve($user, $event))
        ->toThrow(ApiException::class, 'Event is sold out');
    $this->assertDatabaseCount('reservations', 0);
    $this->assertDatabaseCount('tickets', 0);
    expect($event->fresh()->reserved_count)->toBe(1);
});

it('does not decrement the event twice for a cancelled reservation', function (): void {
    $user = User::factory()->create();
    $event = Event::factory()->published()->create(['reserved_count' => 0]);
    $reservation = Reservation::factory()->cancelled()->for($user)->for($event)->create();
    Ticket::factory()->cancelled()->for($reservation)->create();
    $service = app(ReservationService::class);

    expect(fn () => $service->cancel($user, $reservation))
        ->toThrow(ApiException::class, 'Reservation is already cancelled');
    expect($event->fresh()->reserved_count)->toBe(0)
        ->and($reservation->fresh()->status)->toBe(ReservationStatus::Cancelled);
});
