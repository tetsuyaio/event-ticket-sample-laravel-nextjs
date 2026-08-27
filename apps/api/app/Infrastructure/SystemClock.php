<?php

namespace App\Infrastructure;

use App\Contracts\ClockInterface;
use DateTimeImmutable;
use DateTimeZone;

final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone((string) config('app.timezone')));
    }
}
