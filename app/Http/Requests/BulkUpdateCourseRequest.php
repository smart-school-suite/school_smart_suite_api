<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateCourseRequest extends FormRequest
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
            'courses' => 'required|array',
            'courses.*.course_id' => 'required|string|exists:courses,id',
            'courses.*.course_code' => 'sometimes|nullable|string',
            'courses.*.course_title' => 'sometimes|nullable|string',
            'courses.*.specialty_id' => 'sometimes|nullable|string|exists:specialty,id',
            'courses.*.department_id' => 'sometimes|nullable|string|exists:department,id',
            'courses.*.credit' => 'sometimes|nullable|integer',
            'courses.*.semester_id' => 'sometimes|nullable|string|exists:semesters,id',
            'courses.*.level_id' => 'sometimes|nullable|string|exists:education_levels,id',
        ];
    }
}
