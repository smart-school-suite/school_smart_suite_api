<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateStudentRequest extends FormRequest
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
            'level_id' => 'required|string',
            'specialty_id' => 'required|string',
            'department_id' => 'required|string',
            'email' => 'required|email',
            'guadian_id' => 'required|string',
            'student_batch_id' => 'required|string',
            'payment_format' => 'required|string',
        ];
    }
}
