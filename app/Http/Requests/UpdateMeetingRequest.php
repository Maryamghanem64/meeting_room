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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'userId' => 'sometimes|exists:users,id',
            'roomId' => 'sometimes|exists:rooms,id',
            'title' => 'sometimes|string|max:255',
            'agenda' => 'nullable|string',
            'startTime' => 'sometimes|date',
            'endTime' => 'sometimes|date|after_or_equal:startTime',
            'type' => 'sometimes|string|max:50',
            'status' => ['sometimes', 'string', 'max:50', 'regex:/^(pending|ongoing|completed|cancelled|scheduled)$/i']
        ];
    }
}
