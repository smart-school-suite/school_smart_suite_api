<?php

namespace App\Http\Requests\JointCourse;

use Illuminate\Foundation\Http\FormRequest;

class RecommendJointCourseSlotsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'teacher_id' => 'nullable|string',
            'hall_id' => 'nullable|string',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ];
    }
}
