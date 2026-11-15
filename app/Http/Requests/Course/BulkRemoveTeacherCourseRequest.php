<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class BulkRemoveTeacherCourseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'assignments' => 'required|array|min:1',
            'assignments.*.teacher_id' => 'required|string|exists:teachers,id',
            'assignments.*.course_id' => 'required|string|exists:courses,id',
        ];
    }
}
