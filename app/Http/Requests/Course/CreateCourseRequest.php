<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class CreateCourseRequest extends FormRequest
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
            'course_code' => 'required|string',
            'course_title' => 'required|string',
            'specialty_id' => 'required|string|exists:specialties,id',
            'credit' => 'required|integer|max:10',
            'semester_id' => 'required|string|exists:semesters,id',
            'description' => 'nullable|string|max:500'
        ];
    }
}
