<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class CreateTeacherRequest extends FormRequest
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
            'name' => 'required|String',
            'first_name' => 'required|string',
            "last_name" => 'required|string',
            'email' => 'required|email|string',
            'phone' => 'required|string',
            'address' => 'sometimes|nullable|string',
            'gender_id' => 'required|string|exists:genders,id'
        ];
    }
}
