<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Policies\ReservationPolicy;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'event_id', 'status', 'reserved_at', 'cancelled_at'])]
#[UsePolicy(ReservationPolicy::class)]
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory, HasUuids;

    protected $attributes = [
        'status' => ReservationStatus::Reserved->value,
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return HasOne<Ticket, $this> */
    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'reserved_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }
}
