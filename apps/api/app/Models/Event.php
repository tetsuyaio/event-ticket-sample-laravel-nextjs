<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Policies\EventPolicy;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'venue', 'starts_at', 'ends_at', 'capacity', 'reserved_count', 'status', 'created_by'])]
#[UsePolicy(EventPolicy::class)]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, HasUuids;

    protected $attributes = [
        'reserved_count' => 0,
        'status' => EventStatus::Draft->value,
    ];

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<Reservation, $this> */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'capacity' => 'integer',
            'reserved_count' => 'integer',
            'status' => EventStatus::class,
        ];
    }
}
