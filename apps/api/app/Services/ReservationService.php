<?php

namespace App\Services;

use App\Contracts\ClockInterface;
use App\Contracts\Repositories\EventRepositoryInterface;
use App\Contracts\Repositories\ReservationRepositoryInterface;
use App\Contracts\TicketNumberGeneratorInterface;
use App\Contracts\TransactionManagerInterface;
use App\Enums\EventStatus;
use App\Enums\ReservationStatus;
use App\Exceptions\ApiException;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ReservationService
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
        private readonly ReservationRepositoryInterface $reservations,
        private readonly TransactionManagerInterface $transactions,
        private readonly ClockInterface $clock,
        private readonly TicketNumberGeneratorInterface $ticketNumbers,
    ) {}

    public function reserve(User $user, Event $event): Reservation
    {
        return $this->transactions->run(function () use ($user, $event): Reservation {
            $lockedEvent = $this->events->lockById((string) $event->getKey());

            if ($lockedEvent->status !== EventStatus::Published) {
                throw new ApiException('Event is not published', 'EVENT_NOT_PUBLISHED', 409);
            }

            if ($lockedEvent->reserved_count >= $lockedEvent->capacity) {
                throw new ApiException('Event is sold out', 'EVENT_SOLD_OUT', 409);
            }

            if ($this->reservations->existsFor($user, $lockedEvent)) {
                throw new ApiException('You have already reserved this event', 'ALREADY_RESERVED', 409);
            }

            $reservedAt = $this->clock->now();
            $reservation = $this->reservations->create($user, $lockedEvent, $reservedAt);
            $this->reservations->issueTicket($reservation, $this->ticketNumbers->generate(), $reservedAt);
            $this->events->incrementReservedCount($lockedEvent);

            return $this->reservations->loadDetails($reservation);
        }, 3);
    }

    public function cancel(User $user, Reservation $reservation): Reservation
    {
        return $this->transactions->run(function () use ($user, $reservation): Reservation {
            $lockedEvent = $this->events->lockById((string) $reservation->event_id);
            $lockedReservation = $this->reservations->lockById((string) $reservation->getKey());

            if ($lockedReservation->user_id !== $user->id) {
                throw new ApiException('This reservation does not belong to you', 'FORBIDDEN', 403);
            }

            if ($lockedReservation->status === ReservationStatus::Cancelled) {
                throw new ApiException('Reservation is already cancelled', 'RESERVATION_ALREADY_CANCELLED', 409);
            }

            $this->reservations->cancel($lockedReservation, $this->clock->now());
            $this->reservations->cancelTicket($lockedReservation);
            $this->events->decrementReservedCount($lockedEvent);

            return $this->reservations->loadDetails($lockedReservation);
        }, 3);
    }

    /** @return LengthAwarePaginator<int, Reservation> */
    public function searchFor(User $user): LengthAwarePaginator
    {
        return $this->reservations->paginateForUser($user);
    }

    public function show(Reservation $reservation): Reservation
    {
        return $this->reservations->loadDetails($reservation);
    }
}
