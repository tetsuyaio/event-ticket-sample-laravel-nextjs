<?php

namespace App\Http\Requests;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event && ($this->user()?->can('update', $event) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:10000'],
            'venue' => ['sometimes', 'string', 'max:255'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', Rule::enum(EventStatus::class)],
        ];
    }

    /** @return array<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $event = $this->route('event');

            if (! $event instanceof Event) {
                return;
            }

            $startsAt = $this->date('starts_at') ?? $event->starts_at;
            $endsAt = $this->date('ends_at') ?? $event->ends_at;

            if ($startsAt !== null && $endsAt !== null && $endsAt->lessThanOrEqualTo($startsAt)) {
                $validator->errors()->add('ends_at', 'The ends at field must be a date after starts at.');
            }

            if ($this->has('capacity') && (int) $this->input('capacity') < $event->reserved_count) {
                $validator->errors()->add('capacity', 'The capacity must not be less than the reserved count.');
            }
        }];
    }
}
