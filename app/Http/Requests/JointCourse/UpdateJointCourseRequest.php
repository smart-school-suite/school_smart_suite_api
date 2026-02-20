<?php

namespace App\Http\Requests\JointCourse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJointCourseRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_code' => 'sometimes|nullable|string',
            'course_title' => 'sometimes|nullable|string',
            'specialtyIds' => 'sometimes|nullable|array|min:2',
            'specialtyIds.*' => 'exists:specialties,id',
            'description' => 'sometimes|nullable|string',
            'credit' => 'sometimes|nullable|integer',
            'semester_id' => 'sometimes|nullable|string|exists:semesters,id',
            'typeIds' => "sometimes|nullable|array",
            "typeIds.*.type_id" => "sometimes|nullable|exists:course_types,id"
        ];
    }
}
