<?php

use App\Enums\ReservationStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('creates a reservation ticket and increments the reserved count', function (): void {
    $user = User::factory()->create();
    $event = Event::factory()->published()->create(['capacity' => 2]);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/events/{$event->id}/reservations")
        ->assertCreated()
        ->assertJsonPath('data.status', 'RESERVED')
        ->assertJsonPath('data.ticket.status', 'VALID');

    $this->assertDatabaseHas('reservations', ['id' => $response->json('data.id'), 'user_id' => $user->id]);
    $this->assertDatabaseHas('tickets', ['reservation_id' => $response->json('data.id'), 'status' => TicketStatus::Valid->value]);
    expect($event->fresh()->reserved_count)->toBe(1);
});

it('returns 401 when an unauthenticated user reserves', function (): void {
    $event = Event::factory()->published()->create();

    $this->postJson("/api/events/{$event->id}/reservations")
        ->assertUnauthorized()
        ->assertJsonPath('code', 'UNAUTHORIZED');
});

it('rejects draft and sold out events', function (): void {
    $user = User::factory()->create();
    $draft = Event::factory()->create();
    $soldOut = Event::factory()->soldOut()->create();
    Sanctum::actingAs($user);

    $this->postJson("/api/events/{$draft->id}/reservations")
        ->assertConflict()->assertJsonPath('code', 'EVENT_NOT_PUBLISHED');
    $this->postJson("/api/events/{$soldOut->id}/reservations")
        ->assertConflict()->assertJsonPath('code', 'EVENT_SOLD_OUT');
    $this->assertDatabaseCount('reservations', 0);
});

it('rejects a second reservation even after cancellation', function (): void {
    $user = User::factory()->create();
    $event = Event::factory()->published()->create();
    $reservation = Reservation::factory()->cancelled()->for($user)->for($event)->create();
    Ticket::factory()->cancelled()->for($reservation)->create();
    Sanctum::actingAs($user);

    $this->postJson("/api/events/{$event->id}/reservations")
        ->assertConflict()->assertJsonPath('code', 'ALREADY_RESERVED');
    $this->assertDatabaseCount('reservations', 1);
});

it('cancels once and updates the reservation ticket and event atomically', function (): void {
    $user = User::factory()->create();
    $event = Event::factory()->published()->create(['reserved_count' => 1]);
    $reservation = Reservation::factory()->for($user)->for($event)->create();
    $ticket = Ticket::factory()->for($reservation)->create();
    Sanctum::actingAs($user);

    $this->deleteJson("/api/me/reservations/{$reservation->id}")
        ->assertOk()->assertJsonPath('data.status', 'CANCELLED');
    $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => ReservationStatus::Cancelled->value]);
    $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => TicketStatus::Cancelled->value]);
    expect($event->fresh()->reserved_count)->toBe(0);

    $this->deleteJson("/api/me/reservations/{$reservation->id}")
        ->assertConflict()->assertJsonPath('code', 'RESERVATION_ALREADY_CANCELLED');
    expect($event->fresh()->reserved_count)->toBe(0);
});

it('returns 404 when reading another users reservation', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $reservation = Reservation::factory()->for($owner)->create();
    Ticket::factory()->for($reservation)->create();
    Sanctum::actingAs($other);

    $this->getJson("/api/me/reservations/{$reservation->id}")
        ->assertNotFound()->assertJsonPath('code', 'RESERVATION_NOT_FOUND');
});

it('lists only the current users reservations', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $own = Reservation::factory()->for($user)->create();
    Ticket::factory()->for($own)->create();
    $foreign = Reservation::factory()->for($other)->create();
    Ticket::factory()->for($foreign)->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/me/reservations')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $own->id);
});
