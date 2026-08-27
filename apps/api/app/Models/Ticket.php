<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reservation_id', 'ticket_number', 'status', 'issued_at'])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory, HasUuids;

    public const UPDATED_AT = null;

    protected $attributes = [
        'status' => TicketStatus::Valid->value,
    ];

    /** @return BelongsTo<Reservation, $this> */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'issued_at' => 'immutable_datetime',
        ];
    }
}
