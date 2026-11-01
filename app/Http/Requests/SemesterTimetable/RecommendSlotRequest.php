<?php

namespace App\Http\Requests\SemesterTimetable;

use Illuminate\Foundation\Http\FormRequest;

class RecommendSlotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'day' => ['required', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'period_duration_id' => ['required', 'string', 'exists:period_durations,id'],
            'teacher_id' => ['nullable', 'string', 'required_without:course_id', 'exists:teachers,id'],
            'course_id' => ['nullable', 'string', 'required_without:teacher_id', 'exists:courses,id'],
            'hall_id' => ['nullable', 'string', 'exists:halls,id'],
            'school_semester_id' => ['nullable', 'string', 'exists:school_semesters,id'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required_without' => 'Choose a teacher or a course to find free slots for.',
            'course_id.required_without' => 'Choose a teacher or a course to find free slots for.',
        ];
    }
}
