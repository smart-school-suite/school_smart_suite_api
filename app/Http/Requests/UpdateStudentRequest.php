<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|nullable|string',
            'first_name' => 'sometimes|nullable|string',
            'last_name' => 'sometimes|nullable|string',
            'gender' => 'sometimes|nullable|string',
            'phone_one' => 'sometimes|nullable|string',
            'level_id' => 'sometimes|nullable|string',
            'specialty_id' => 'sometimes|nullable|string',
            'department_id' => 'sometimes|nullable|string',
            'email' => 'sometimes|nullable|email',
            'guadian_id' => 'sometimes|nullable|string',
            'student_batch_id' => 'sometimes|nullable|string'
        ];
    }
}
