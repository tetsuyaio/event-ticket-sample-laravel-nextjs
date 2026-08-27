<?php

namespace App\Contracts\Repositories;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Event>
     */
    public function paginate(array $filters, bool $includeUnpublished): LengthAwarePaginator;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Event;

    /** @param array<string, mixed> $attributes */
    public function update(Event $event, array $attributes): Event;

    public function delete(Event $event): void;

    public function lockById(string $eventId): Event;

    public function incrementReservedCount(Event $event): void;

    public function decrementReservedCount(Event $event): void;

    public function loadDetails(Event $event): Event;
}
