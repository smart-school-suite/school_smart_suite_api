<?php

namespace App\Http\Requests;

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
            'department_id' => 'sometimes|nullable|string',
            'registration_fee' => 'sometimes|nullable|decimal:0, 2',
            'school_fee' => 'sometimes|nullable|decimal:0, 2',
            'level_id' => 'sometimes|nullable|string|exists:education_levels,id'
        ];
    }
}
