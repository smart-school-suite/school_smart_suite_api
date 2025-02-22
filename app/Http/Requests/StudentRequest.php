<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
   // {
       // return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'DOB' => 'required|string',
            'gender' => 'required|string',
            'phone_one' => 'required|string',
            'level_id' => 'required|string|exists:education_levels,id',
            'specialty_id' => 'required|string|exists:specialty,id',
            'department_id' => 'required|string|exists:department,id',
            'email' => 'required|email',
            'guadian_id' => 'required|string|exists:parents,id',
            'password' => 'required|string|min:8',
            'student_batch_id' => 'required|string|exists:student_batch,id'
        ];
    }
}
