<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'venue' => $this->venue,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'capacity' => $this->capacity,
            'reserved_count' => $this->reserved_count,
            'remaining_capacity' => max(0, $this->capacity - $this->reserved_count),
            'status' => $this->status->value,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
