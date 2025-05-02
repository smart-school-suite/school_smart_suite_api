<?php

namespace App\Http\Requests\Hod;

use Illuminate\Foundation\Http\FormRequest;

class CreateHodRequest extends FormRequest
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
            'department_id' => 'required|string|exists:department,id',
            'hodable_id' => 'required|string',
        ];
    }
}
