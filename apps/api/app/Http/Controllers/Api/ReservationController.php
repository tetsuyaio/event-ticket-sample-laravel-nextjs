<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Event;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservations) {}

    public function store(Request $request, Event $event): JsonResponse
    {
        Gate::authorize('reserve', $event);
        $reservation = $this->reservations->reserve($request->user(), $event);

        return (new ReservationResource($reservation))->response()->setStatusCode(201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Reservation::class);
        $reservations = $this->reservations->searchFor($request->user());

        return ReservationResource::collection($reservations);
    }

    public function show(Request $request, Reservation $reservation): ReservationResource
    {
        Gate::authorize('view', $reservation);

        return new ReservationResource($this->reservations->show($reservation));
    }

    public function destroy(Request $request, Reservation $reservation): ReservationResource
    {
        Gate::authorize('delete', $reservation);

        return new ReservationResource($this->reservations->cancel($request->user(), $reservation));
    }
}
