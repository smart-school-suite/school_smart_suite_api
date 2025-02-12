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
            'course_code' => 'sometimes|required|string',
            'course_title' => 'sometimes|required|string',
            'specialty_id' => 'sometimes|required|string',
            'department_id' => 'sometimes|required|string',
            'credit' => 'sometimes|required|integer',
            'semester_id' => 'sometimes|required|string',
            'level_id' => 'sometimes|required|string',
        ];
    }
}
