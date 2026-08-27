<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Reserved = 'RESERVED';
    case Cancelled = 'CANCELLED';
}
