<?php

namespace App\Contracts;

interface TicketNumberGeneratorInterface
{
    public function generate(): string;
}
