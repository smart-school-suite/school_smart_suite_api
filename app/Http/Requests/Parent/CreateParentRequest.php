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
            'email' => 'required|string|email',
            'phone_one' => 'string|required',
            'relationship_to_student' => 'required|string',
            'preferred_language' => 'required|string',
        ];
    }
}
