<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
            'course_code' => 'sometimes|nullable|string',
            'course_title' => 'sometimes|nullable|string',
            'specialty_id' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'credit' => 'sometimes|nullable|integer',
            'semester_id' => 'sometimes|nullable|string'
        ];
    }
}
