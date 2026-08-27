<?php

namespace App\Repositories;

use App\Contracts\Repositories\ReservationRepositoryInterface;
use App\Enums\ReservationStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentReservationRepository implements ReservationRepositoryInterface
{
    public function existsFor(User $user, Event $event): bool
    {
        return Reservation::query()->whereBelongsTo($user)->whereBelongsTo($event)->exists();
    }

    public function create(User $user, Event $event, DateTimeInterface $reservedAt): Reservation
    {
        return Reservation::query()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => ReservationStatus::Reserved,
            'reserved_at' => $reservedAt,
        ]);
    }

    public function issueTicket(Reservation $reservation, string $ticketNumber, DateTimeInterface $issuedAt): void
    {
        $reservation->ticket()->create([
            'ticket_number' => $ticketNumber,
            'status' => TicketStatus::Valid,
            'issued_at' => $issuedAt,
        ]);
    }

    public function lockById(string $reservationId): Reservation
    {
        return Reservation::query()->whereKey($reservationId)->lockForUpdate()->firstOrFail();
    }

    public function cancel(Reservation $reservation, DateTimeInterface $cancelledAt): void
    {
        $reservation->update([
            'status' => ReservationStatus::Cancelled,
            'cancelled_at' => $cancelledAt,
        ]);
    }

    public function cancelTicket(Reservation $reservation): void
    {
        $reservation->ticket()->update(['status' => TicketStatus::Cancelled]);
    }

    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Reservation::query()
            ->whereBelongsTo($user)
            ->with(['event.creator', 'ticket'])
            ->latest('reserved_at')
            ->paginate($perPage);
    }

    public function loadDetails(Reservation $reservation): Reservation
    {
        return $reservation->load(['event.creator', 'ticket']);
    }
}
