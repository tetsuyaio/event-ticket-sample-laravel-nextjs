<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchEventsRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    public function __construct(private readonly EventService $events) {}

    /**
     * Display a listing of the resource.
     */
    public function index(SearchEventsRequest $request): AnonymousResourceCollection
    {
        $events = $this->events->search($request->validated(), $request->user('sanctum'));

        return EventResource::collection($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = $this->events->create($request->user(), $request->validated());

        return (new EventResource($event))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Event $event): EventResource
    {
        return new EventResource($this->events->show($event, $request->user('sanctum')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event): EventResource
    {
        return new EventResource($this->events->update($event, $request->validated()));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): Response
    {
        Gate::authorize('delete', $event);
        $this->events->delete($event);

        return response()->noContent();
    }
}
