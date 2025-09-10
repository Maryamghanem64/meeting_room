<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeetingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'userId' => $this->user_id ?? $this->userId,
            'roomId' => $this->room_id ?? $this->roomId,
            'startTime' => $this->start_time ?? $this->startTime,
            'endTime' => $this->end_time ?? $this->endTime,
        ]);

        // Handle status if it's an array or scalar
        if (isset($this->status)) {
            if ($this->status === null) {
                $this->merge(['status' => 'pending']);
            }
            if (is_array($this->status)) {
                $status = $this->status[0] ?? null;
                $this->merge(['status' => is_string($status) ? $status : (is_scalar($status) ? (string) $status : null)]);
            } elseif (is_scalar($this->status)) {
                $this->merge(['status' => (string) $this->status]);
            } else {
                $this->merge(['status' => null]);
            }
            if (is_string($this->status)) {
                $status = strtolower($this->status);
                if (!in_array($status, ['pending', 'ongoing', 'completed', 'cancelled', 'scheduled'])) {
                    $status = 'pending';
                }
                $this->merge(['status' => $status]);
            }
        }

        // Handle empty strings or "null" strings as null
        if ($this->startTime === '' || $this->startTime === 'null') {
            $this->merge(['startTime' => null]);
        }
        if ($this->endTime === '' || $this->endTime === 'null') {
            $this->merge(['endTime' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'userId' => 'sometimes|exists:users,id',
            'roomId' => 'sometimes|integer|exists:rooms,Id',
            'title' => 'sometimes|string|max:255',
            'agenda' => 'nullable|string',
            'startTime' => 'sometimes|nullable|date',
            'endTime' => 'sometimes|nullable|date|after_or_equal:startTime',
            'type' => 'sometimes|string|max:50',
            'status' => ['sometimes', 'string', 'max:50', 'in:pending,ongoing,completed,cancelled,scheduled']
        ];
    }
}
