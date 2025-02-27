<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string',
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'DOB' => 'sometimes|string',
            'gender' => 'sometimes|string',
            'phone_one' => 'sometimes|string',
            'level_id' => 'sometimes|string',
            'specialty_id' => 'sometimes|string',
            'department_id' => 'sometimes|string',
            'email' => 'sometimes|email',
            'guadian_id' => 'sometimes|string',
            'password' => 'sometimes|string|min:8',
            'student_batch_id' => 'sometimes|string'
        ];
    }
}
