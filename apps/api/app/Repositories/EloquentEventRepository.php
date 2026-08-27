<?php

namespace App\Repositories;

use App\Contracts\Repositories\EventRepositoryInterface;
use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentEventRepository implements EventRepositoryInterface
{
    public function paginate(array $filters, bool $includeUnpublished): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? 'starts_at';
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $sortColumn = ltrim($sort, '-');

        return Event::query()
            ->with('creator')
            ->when(! $includeUnpublished, fn ($query) => $query->where('status', EventStatus::Published))
            ->when($filters['keyword'] ?? null, function ($query, string $keyword): void {
                $pattern = '%'.mb_strtolower($keyword).'%';
                $query->where(function ($query) use ($pattern): void {
                    $query->whereRaw('LOWER(title) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(venue) LIKE ?', [$pattern]);
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['starts_from'] ?? null, fn ($query, string $date) => $query->where('starts_at', '>=', $date))
            ->when($filters['starts_to'] ?? null, fn ($query, string $date) => $query->where('starts_at', '<=', $date))
            ->orderBy($sortColumn, $direction)
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    public function create(array $attributes): Event
    {
        return Event::query()->create($attributes);
    }

    public function update(Event $event, array $attributes): Event
    {
        $event->update($attributes);

        return $event;
    }

    public function delete(Event $event): void
    {
        $event->delete();
    }

    public function lockById(string $eventId): Event
    {
        return Event::query()->whereKey($eventId)->lockForUpdate()->firstOrFail();
    }

    public function incrementReservedCount(Event $event): void
    {
        $event->increment('reserved_count');
    }

    public function decrementReservedCount(Event $event): void
    {
        Event::query()
            ->whereKey($event->getKey())
            ->where('reserved_count', '>', 0)
            ->decrement('reserved_count');
    }

    public function loadDetails(Event $event): Event
    {
        return $event->load('creator');
    }
}
