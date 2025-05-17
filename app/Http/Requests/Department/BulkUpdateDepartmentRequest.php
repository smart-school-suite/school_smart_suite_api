<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'departments' => 'required|array',
            'departments.*.department_id' => 'required|string|exists:department,id',
            'departments.*.department_name' => 'sometimes|nullable|string',
            'departments.*.description' => 'sometimes|nullable|string',
        ];
    }
}
