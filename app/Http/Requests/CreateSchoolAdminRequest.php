<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSchoolAdminRequest extends FormRequest
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
            'name' => "required|string",
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|string',
            'employment_status' => 'required|string',
            'work_location' => 'required|string',
            'position' => 'required|string',
            'hire_date' => 'required|date',
            'salary' => 'required',
        ];
    }
}
