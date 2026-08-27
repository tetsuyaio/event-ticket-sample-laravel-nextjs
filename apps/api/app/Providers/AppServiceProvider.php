<?php

namespace App\Providers;

use App\Contracts\AccessTokenManagerInterface;
use App\Contracts\ClockInterface;
use App\Contracts\Repositories\EventRepositoryInterface;
use App\Contracts\Repositories\ReservationRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\TicketNumberGeneratorInterface;
use App\Contracts\TransactionManagerInterface;
use App\Infrastructure\DatabaseTransactionManager;
use App\Infrastructure\SanctumAccessTokenManager;
use App\Infrastructure\SystemClock;
use App\Infrastructure\UuidTicketNumberGenerator;
use App\Repositories\EloquentEventRepository;
use App\Repositories\EloquentReservationRepository;
use App\Repositories\EloquentUserRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);
        $this->app->bind(ReservationRepositoryInterface::class, EloquentReservationRepository::class);
        $this->app->bind(TransactionManagerInterface::class, DatabaseTransactionManager::class);
        $this->app->bind(AccessTokenManagerInterface::class, SanctumAccessTokenManager::class);
        $this->app->bind(ClockInterface::class, SystemClock::class);
        $this->app->bind(TicketNumberGeneratorInterface::class, UuidTicketNumberGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('authentication', function (Request $request): Limit {
            $email = mb_strtolower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }
}
