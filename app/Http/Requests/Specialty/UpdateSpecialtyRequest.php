<?php

namespace App\Http\Requests\Specialty;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpecialtyRequest extends FormRequest
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
            'specialty_name' => 'sometimes|nullable|string',
            'department_id' => 'sometimes|nullable|string|exists:departments,id',
            'registration_fee' => 'sometimes|nullable|decimal:0, 2',
            'school_fee' => 'sometimes|nullable|decimal:0, 2',
            'level_id' => 'sometimes|nullable|string|exists:levels,id',
            'description' => 'sometimes|nullable|string'
        ];
    }
}
