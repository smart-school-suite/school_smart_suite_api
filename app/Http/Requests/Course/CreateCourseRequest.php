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
            'specialty_id' => 'required|string',
            'credit' => 'required|integer',
            'semester_id' => 'required|string',
            'description' => 'nullable|string'
        ];
    }
}
