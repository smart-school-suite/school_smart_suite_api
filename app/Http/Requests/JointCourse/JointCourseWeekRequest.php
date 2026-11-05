<?php

namespace App\Http\Requests\JointCourse;

use Illuminate\Foundation\Http\FormRequest;

class JointCourseWeekRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'duration_minutes' => 'required|integer|min:15|max:480',
            'days' => 'nullable|array|min:1',
            'days.*' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'teacher_id' => 'nullable|string',
            'hall_id' => [$this->routeIs('jointCourseTimetable.slots.week') ? 'required' : 'nullable', 'string'],
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ];
    }
}
