<?php

namespace App\Http\Requests\JointCourse;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\SanitizesRichText;

class CreateJointCourseRequest extends FormRequest
{
    use SanitizesRichText;



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_code' => 'required|string|max:255',
            'course_title' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'credit' => 'required|numeric|min:0',
            'semester_id' => 'required|exists:semesters,id',
            'specialtyIds' => 'required|array|min:2',
            'specialtyIds.*' => 'exists:specialties,id',
            'typeIds' => "required|array|min:1",
            "typeIds.*.type_id" => "required|string|exists:course_types,id"
        ];
    }
}
