<?php

namespace App\Contracts\Repositories;

use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReservationRepositoryInterface
{
    public function existsFor(User $user, Event $event): bool;

    public function create(User $user, Event $event, DateTimeInterface $reservedAt): Reservation;

    public function issueTicket(Reservation $reservation, string $ticketNumber, DateTimeInterface $issuedAt): void;

    public function lockById(string $reservationId): Reservation;

    public function cancel(Reservation $reservation, DateTimeInterface $cancelledAt): void;

    public function cancelTicket(Reservation $reservation): void;

    /** @return LengthAwarePaginator<int, Reservation> */
    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator;

    public function loadDetails(Reservation $reservation): Reservation;
}
