<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                'title' => 'sometimes|nullable|string|max:255',
                'start_date' => 'sometimes|nullable|date|after:now',
                'end_date' => 'sometimes|nullable|date|after:start_date',
                'location' => 'sometimes|nullable|string|max:255',
                'description' => 'sometimes|nullable|string',
                'organizer' => 'sometimes|nullable|string|max:255',
                'category' => 'sometimes|nullable|string|max:255',
                'duration' => 'sometimes|nullable|string',
                'recipients' => 'sometimes|nullable|string'
            ], [
                'title.required' => 'The event title is required.',
                'start_date.required' => 'The start date is required.',
                'start_date.after' => 'The start date must be in the future.',
                'end_date.required' => 'The end date is required.',
                'end_date.after' => 'The end date must be after the start date.',
                'location.required' => 'The location is required.',
                'description.required' => 'The description is required.',
                'organizer.required' => 'An organizer name is required.',
                'category.required' => 'A category for the event is required.',
                'duration.required' => 'Duration is required and should be a positive integer.'
            ]
        ];
    }
}
