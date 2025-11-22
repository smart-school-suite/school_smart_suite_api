<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateCourseRequest extends FormRequest
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
    public function rules()
    {
        return [
            'courses' => 'required|array',
            'courses.*.course_id' => 'required|string|exists:courses,id',
            'courses.*.course_code' => 'sometimes|nullable|string',
            'courses.*.course_title' => 'sometimes|nullable|string',
            'courses.*.description' => 'sometimes|nullable|string',
            'courses.*.specialty_id' => 'sometimes|nullable|string|exists:specialties,id',
            'courses.*.credit' => 'sometimes|nullable|integer',
            'courses.*.semester_id' => 'sometimes|nullable|string|exists:semesters,id',
        ];
    }
}
