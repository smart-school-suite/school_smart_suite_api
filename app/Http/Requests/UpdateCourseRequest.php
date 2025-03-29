<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
    //{
      //  return false;
    //}

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
            'specialty_id' => 'sometimes|nullable|string',
            'department_id' => 'sometimes|nullable|string',
            'credit' => 'sometimes|nullable|integer',
            'semester_id' => 'sometimes|nullable|string',
            'level_id' => 'sometimes|nullable|string',
        ];
    }
}
