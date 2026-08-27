<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Valid = 'VALID';
    case Cancelled = 'CANCELLED';
}
