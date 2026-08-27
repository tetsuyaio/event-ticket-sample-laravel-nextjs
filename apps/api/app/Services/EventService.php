<?php

namespace App\Services;

use App\Contracts\Repositories\EventRepositoryInterface;
use App\Enums\EventStatus;
use App\Exceptions\ApiException;
use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EventService
{
    public function __construct(private readonly EventRepositoryInterface $events) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Event>
     */
    public function search(array $filters, ?User $viewer): LengthAwarePaginator
    {
        return $this->events->paginate($filters, $viewer?->isAdmin() ?? false);
    }

    /** @param array<string, mixed> $attributes */
    public function create(User $creator, array $attributes): Event
    {
        $event = $this->events->create([
            ...$attributes,
            'created_by' => $creator->id,
        ]);

        return $this->events->loadDetails($event);
    }

    public function show(Event $event, ?User $viewer): Event
    {
        if (! ($viewer?->isAdmin() ?? false) && $event->status !== EventStatus::Published) {
            throw new ApiException('Event not found', 'EVENT_NOT_FOUND', 404);
        }

        return $this->events->loadDetails($event);
    }

    /** @param array<string, mixed> $attributes */
    public function update(Event $event, array $attributes): Event
    {
        return $this->events->loadDetails($this->events->update($event, $attributes));
    }

    public function delete(Event $event): void
    {
        $this->events->delete($event);
    }
}
