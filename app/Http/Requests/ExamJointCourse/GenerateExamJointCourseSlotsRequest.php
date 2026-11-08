<?php

namespace App\Http\Requests\ExamJointCourse;

use Illuminate\Foundation\Http\FormRequest;

class GenerateExamJointCourseSlotsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // How long the joint exam lasts.
            'duration' => 'required|integer|min:15|max:720',
            // Only these dates (default: every date the linked exam periods share).
            'dates' => 'sometimes|nullable|array',
            'dates.*' => 'required|date_format:Y-m-d',
            // Daily exam hours to search in (default 08:00 – 17:00).
            'start_time' => 'sometimes|nullable|date_format:H:i',
            'end_time' => 'sometimes|nullable|date_format:H:i|after:start_time',
            'step_minutes' => 'sometimes|nullable|integer|in:15,30,60',
        ];
    }
}
