<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
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
                'title' => 'required|string|max:255',
                'start_date' => 'required|date|after:now',
                'end_date' => 'required|date|after:start_date',
                'location' => 'required|string|max:255',
                'description' => 'required|string',
                'organizer' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'duration' => 'required|string',
                'recipients' => 'required|string'
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
