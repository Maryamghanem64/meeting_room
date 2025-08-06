<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingRequest extends FormRequest
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
            'userId' => 'required|exists:users,id',
            'roomId' => 'required|exists:rooms,id',
            'title' => 'required|string|max:255',
            'agenda' => 'nullable|string',
            'startTime' => 'required|date',
            'endTime' => 'required|date|after_or_equal:startTime',
            'type' => 'required|string|max:50',
            'status' => 'required|string|max:50'
        ];
    }
}
