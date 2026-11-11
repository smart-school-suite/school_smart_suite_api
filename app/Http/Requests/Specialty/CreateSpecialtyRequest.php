<?php

namespace App\Http\Requests\Specialty;

use Illuminate\Foundation\Http\FormRequest;

class CreateSpecialtyRequest extends FormRequest
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
            'specialty_name' => 'required|string',
            'department_id' => 'required|string|exists:departments,id',
            // When left out, they default to the level's fee from the
            // "Registration/Tuition Fees by Academic Level" settings.
            'registration_fee' => 'sometimes|nullable|decimal:0, 2',
            'school_fee' => 'sometimes|nullable|decimal:0, 2',
            'level_id' => 'required|string|exists:levels,id',
            'description' => 'sometimes|nullable|string',
        ];
    }
}
