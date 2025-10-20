<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:5000',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:2048',
            'organizer' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'status' => ['required', 'string', Rule::in(['active', 'scheduled'])],
            'start_date' => 'required|date_format:Y-m-d H:i',
            'end_date' => 'required|date_format:Y-m-d H:i|after_or_equal:start_date',
            'published_at' => 'nullable|date_format:Y-m-d H:i',
            'tag_ids' => 'required|array',
            'tag_ids.*.tag_id' => 'string|exists:event_tags,id',
            'event_category_id' => 'string|required|exists:event_categories,id',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*.teacher_id' => 'required|string|exists:teacher,id',
            'school_admin_ids' => 'nullable|array',
            'school_admin_ids.*.school_admin_id' => 'required|string|exists:school_admin,id',

            'student_audience' => 'nullable|array',
            'student_audience.*.student_audience_id' => 'required|string|exists:specialty,id',

        ];

        return $rules;
    }

}
