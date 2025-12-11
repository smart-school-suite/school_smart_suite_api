<?php

namespace App\Http\Requests\ActivationCode;

use Illuminate\Foundation\Http\FormRequest;

class ActivateStudentAccountRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|string|exists:students,id',
            'activation_code' => 'required|string'
        ];
    }
}
