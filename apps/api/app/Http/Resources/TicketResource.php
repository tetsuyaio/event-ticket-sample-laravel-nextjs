<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservation_id' => $this->reservation_id,
            'ticket_number' => $this->ticket_number,
            'status' => $this->status->value,
            'issued_at' => $this->issued_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
