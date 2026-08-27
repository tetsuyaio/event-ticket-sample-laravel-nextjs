<?php

use App\Http\Middleware\LogApiRequest;
use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('api', LogApiRequest::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'The given data was invalid.',
                'code' => 'VALIDATION_ERROR',
                'errors' => $exception->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Unauthenticated.',
                'code' => 'UNAUTHORIZED',
                'errors' => null,
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $exception->status() ?? 403;

            return response()->json([
                'message' => $status === 404 ? 'Reservation not found' : 'This action is unauthorized.',
                'code' => $status === 404 ? 'RESERVATION_NOT_FOUND' : 'FORBIDDEN',
                'errors' => null,
            ], $status);
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'This action is unauthorized.',
                'code' => 'FORBIDDEN',
                'errors' => null,
            ], 403);
        });

        $exceptions->render(function (HttpException $exception, Request $request) {
            if (! $request->is('api/*') || ! in_array($exception->getStatusCode(), [403, 404], true)) {
                return null;
            }

            $isNotFound = $exception->getStatusCode() === 404;
            $isReservation = str_contains($request->path(), 'me/reservations/');

            return response()->json([
                'message' => $isNotFound
                    ? ($isReservation ? 'Reservation not found' : 'Event not found')
                    : 'This action is unauthorized.',
                'code' => $isNotFound
                    ? ($isReservation ? 'RESERVATION_NOT_FOUND' : 'EVENT_NOT_FOUND')
                    : 'FORBIDDEN',
                'errors' => null,
            ], $exception->getStatusCode());
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            [$message, $code] = match ($exception->getModel()) {
                Event::class => ['Event not found', 'EVENT_NOT_FOUND'],
                Reservation::class => ['Reservation not found', 'RESERVATION_NOT_FOUND'],
                default => ['Resource not found', 'NOT_FOUND'],
            };

            return response()->json([
                'message' => $message,
                'code' => $code,
                'errors' => null,
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $isReservation = str_contains($request->path(), 'me/reservations/');

            return response()->json([
                'message' => $isReservation ? 'Reservation not found' : 'Event not found',
                'code' => $isReservation ? 'RESERVATION_NOT_FOUND' : 'EVENT_NOT_FOUND',
                'errors' => null,
            ], 404);
        });
    })->create();
