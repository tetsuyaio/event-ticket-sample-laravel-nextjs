<?php

namespace App\Infrastructure;

use App\Contracts\TicketNumberGeneratorInterface;
use Illuminate\Support\Str;

final class UuidTicketNumberGenerator implements TicketNumberGeneratorInterface
{
    public function generate(): string
    {
        return 'TKT-'.strtoupper(str_replace('-', '', (string) Str::uuid()));
    }
}
