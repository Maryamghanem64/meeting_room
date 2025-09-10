<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreAttendeeRequest extends FormRequest
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
        // Log the incoming request data for debugging
        Log::info('StoreAttendeeRequest input data:', $this->all());

        $this->merge([
            'meetingId' => $this->meeting_id ?? $this->meetingId,
            'userId' => $this->user_id ?? $this->userId,
            'isPresent' => filter_var($this->isPresent ?? $this->is_present ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'meetingId' => 'required|exists:meetings,Id',
            'userId' => 'required|exists:users,Id',
            'isPresent' => 'required|boolean'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log the validation errors for debugging
        Log::error('StoreAttendeeRequest validation failed:', $validator->errors()->toArray());

        parent::failedValidation($validator);
    }
}
