<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class ValidateDepartmentIdRequest extends FormRequest
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
           "departmentIds" => "required|array",
           "departmentIds.*.department_id" => "required|string|exists:departments,id"
        ];
    }
}
