<?php

namespace App\Http\Requests\JointCourse;

use Illuminate\Foundation\Http\FormRequest;

class GenerateJointCourseTimetableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Every option is one period of this duration; days may override it.
            'period_duration_id' => 'required|string|exists:period_durations,id',
            'days' => 'required|array|min:1|max:7',
            'days.*.day' => 'required|string|distinct|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'days.*.period_duration_id' => 'nullable|string|exists:period_durations,id',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ];
    }
}
