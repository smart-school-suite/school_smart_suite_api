<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateDepartmentRequest extends FormRequest
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
            'departments' => 'required|array',
            'departments.*.id' => 'required|string|exists:department,id',
            'departments.*.department_name' => 'sometimes|nullable|string',
            'departments.*.description' => 'sometimes|nullable|string'
        ];
    }
}
