<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateEventContentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|nullable|string|max:200',
            'description' => 'sometimes|nullable|string|max:5000',
            'background_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:2048',
            'organizer' => 'sometimes|nullable|string|max:255',
            'location' => 'sometimes|nullable|string|max:255',
            'start_date' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'end_date' => 'sometimes|nullable|date_format:Y-m-d H:i|after_or_equal:start_date',
            'event_category_id' => 'sometimes|nullable|exists:event_categories,id',
            'tag_id' => 'sometimes|nullable|exists:event_tags,id',
        ];
    }
}
