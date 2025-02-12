<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCourseRequest extends FormRequest
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
                  'course_code' => 'required|string',
                  'course_title' => 'required|string',
                  'specialty_id' => 'required|string',
                  'department_id' => 'required|string',
                  'credit' => 'required|integer',
                  'semester_id' => 'required|string',
                  'level_id' => 'required|string'
        ];
    }
}
