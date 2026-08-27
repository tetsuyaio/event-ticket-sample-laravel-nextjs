<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/signup', [AuthController::class, 'signup'])->middleware('throttle:authentication');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:authentication');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/events', [EventController::class, 'store']);
    Route::patch('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
    Route::post('/events/{event}/reservations', [ReservationController::class, 'store']);
    Route::get('/me/reservations', [ReservationController::class, 'index']);
    Route::get('/me/reservations/{reservation}', [ReservationController::class, 'show']);
    Route::delete('/me/reservations/{reservation}', [ReservationController::class, 'destroy']);
});
