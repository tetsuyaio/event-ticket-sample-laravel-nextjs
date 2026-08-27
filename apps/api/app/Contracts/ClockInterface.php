<?php

namespace App\Contracts;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
