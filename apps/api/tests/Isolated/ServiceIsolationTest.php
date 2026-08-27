<?php

use App\Contracts\AccessTokenManagerInterface;
use App\Contracts\ClockInterface;
use App\Contracts\Repositories\EventRepositoryInterface;
use App\Contracts\Repositories\ReservationRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\TicketNumberGeneratorInterface;
use App\Contracts\TransactionManagerInterface;
use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Models\Event;
use App\Models\User;
use App\Services\AuthService;
use App\Services\ReservationService;
use Illuminate\Contracts\Hashing\Hasher;
use Mockery\MockInterface;

afterEach(fn () => Mockery::close());

it('signs up through ports without booting Laravel or accessing a database', function (): void {
    /** @var UserRepositoryInterface&MockInterface $users */
    $users = Mockery::mock(UserRepositoryInterface::class);
    /** @var AccessTokenManagerInterface&MockInterface $tokens */
    $tokens = Mockery::mock(AccessTokenManagerInterface::class);
    /** @var Hasher&MockInterface $hasher */
    $hasher = Mockery::mock(Hasher::class);
    $user = new User;
    $user->forceFill([
        'id' => 'user-id',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => UserRole::User,
    ]);

    $users->shouldReceive('emailExists')->once()->with('test@example.com')->andReturnFalse();
    $users->shouldReceive('create')->once()->with(Mockery::on(
        fn (array $attributes): bool => $attributes['role'] === UserRole::User,
    ))->andReturn($user);
    $tokens->shouldReceive('issue')->once()->with($user, 'bff')->andReturn('plain-token');

    $result = (new AuthService($users, $tokens, $hasher))->signup([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    expect($result->user)->toBe($user)
        ->and($result->token)->toBe('plain-token');
});

it('rejects a sold out event without calling a persistence write', function (): void {
    /** @var EventRepositoryInterface&MockInterface $events */
    $events = Mockery::mock(EventRepositoryInterface::class);
    /** @var ReservationRepositoryInterface&MockInterface $reservations */
    $reservations = Mockery::mock(ReservationRepositoryInterface::class);
    /** @var TransactionManagerInterface&MockInterface $transactions */
    $transactions = Mockery::mock(TransactionManagerInterface::class);
    /** @var ClockInterface&MockInterface $clock */
    $clock = Mockery::mock(ClockInterface::class);
    /** @var TicketNumberGeneratorInterface&MockInterface $ticketNumbers */
    $ticketNumbers = Mockery::mock(TicketNumberGeneratorInterface::class);

    $user = new User;
    $user->forceFill(['id' => 'user-id']);
    $event = new Event;
    $event->forceFill([
        'id' => 'event-id',
        'status' => EventStatus::Published,
        'capacity' => 1,
        'reserved_count' => 1,
    ]);

    $transactions->shouldReceive('run')
        ->once()
        ->with(Mockery::type(Closure::class), 3)
        ->andReturnUsing(fn (Closure $callback): mixed => $callback());
    $events->shouldReceive('lockById')->once()->with('event-id')->andReturn($event);
    $reservations->shouldNotReceive('create');
    $reservations->shouldNotReceive('issueTicket');
    $events->shouldNotReceive('incrementReservedCount');

    $service = new ReservationService($events, $reservations, $transactions, $clock, $ticketNumbers);

    expect(fn () => $service->reserve($user, $event))
        ->toThrow(ApiException::class, 'Event is sold out');
});
