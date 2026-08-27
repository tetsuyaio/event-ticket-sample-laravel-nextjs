<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'DRAFT';
    case Published = 'PUBLISHED';
    case Closed = 'CLOSED';
    case Cancelled = 'CANCELLED';
}
