<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            [
                'title' => 'sometimes|string|max:255',
                'start_date' => 'sometimes|date|after:now',
                'end_date' => 'sometimes|date|after:start_date',
                'location' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'organizer' => 'sometimes|string|max:255',
                'category' => 'sometimes|string|max:255',
                'duration' => 'sometimes|string'
            ], [
                'title.sometimes' => 'The event title is required.',
                'start_date.sometimes' => 'The start date is required.',
                'start_date.after' => 'The start date must be in the future.',
                'end_date.sometimes' => 'The end date is required.',
                'end_date.after' => 'The end date must be after the start date.',
                'location.sometimes' => 'The location is required.',
                'description.sometimes' => 'The description is required.',
                'organizer.sometimes' => 'An organizer name is required.',
                'category.sometimes' => 'A category for the event is required.',
                'duration.sometimes' => 'Duration is required and should be a positive integer.'
            ]
        ];
    }
}
