<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'event_id' => 'required|exists:events,id',
            'tickets_booked' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'event_id.required' => 'The event ID is required.',
            'event_id.exists' => 'The selected event does not exist.',
            'tickets_booked.required' => 'The number of tickets is required.',
            'tickets_booked.integer' => 'The number of tickets must be an integer.',
            'tickets_booked.min' => 'The number of tickets must be at least 1.',
        ];
    }
}
