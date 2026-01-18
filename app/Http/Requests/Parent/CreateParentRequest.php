<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class CreateParentRequest extends FormRequest
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
            'name' => 'string|required',
            'address' => 'required|string',
            'phone' => 'string|required',
            'preferred_contact_method' => 'required|string',
            'preferred_language' => 'required|string',
        ];
    }
}
