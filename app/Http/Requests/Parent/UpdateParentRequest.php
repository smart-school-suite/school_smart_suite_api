<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParentRequest extends FormRequest
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
            'name' => 'string|sometimes|nullable',
            'address' => 'string|sometimes|nullable',
            'phone' => 'string|sometimes|nullable',
            'preferred_contact_method' => 'string|sometimes|nullable',
            'preferred_language' => 'string|sometimes|nullable',
        ];
    }
}
