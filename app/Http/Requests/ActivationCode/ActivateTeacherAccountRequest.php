<?php

namespace App\Http\Requests\ActivationCode;

use Illuminate\Foundation\Http\FormRequest;

class ActivateTeacherAccountRequest extends FormRequest
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
            'teacher_id' => 'required|string|exists:teachers,id',
            'activation_code' => 'required|string'
        ];
    }
}
