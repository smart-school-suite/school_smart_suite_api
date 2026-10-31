<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventContentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|nullable|string|max:200',
            'description' => 'sometimes|nullable|string|max:5000',
            'organizer' => 'sometimes|nullable|string|max:255',
            'location' => 'sometimes|nullable|string|max:255',
            'start_date' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'end_date' => 'sometimes|nullable|date_format:Y-m-d H:i|after_or_equal:start_date',
            'event_category_id' => 'sometimes|nullable|uuid|exists:event_categories,id',
            'tag_ids' => 'sometimes|nullable|array',
            'tag_ids.*.tag_id' => 'required|uuid|exists:event_tags,id',

            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'uuid|exists:school_event_pics,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'images' => 'Event Images',
            'images.*' => 'Event Image',
        ];
    }

    public function messages(): array
    {
        return [
            'images.max' => 'An event can have at most 5 images.',
            'images.*.max' => 'Each event image must be 2 MB or smaller.',
        ];
    }
}
