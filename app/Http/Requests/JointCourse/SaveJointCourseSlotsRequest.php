<?php

namespace App\Http\Requests\JointCourse;

use Illuminate\Foundation\Http\FormRequest;

class SaveJointCourseSlotsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'slots' => 'present|array|max:20',
            'slots.*.day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'slots.*.start_time' => 'required|date_format:H:i',
            'slots.*.end_time' => 'required|date_format:H:i|after:slots.*.start_time',
            'slots.*.hall_id' => 'required|string',
            'slots.*.teacher_id' => 'nullable|string',
        ];
    }
}
