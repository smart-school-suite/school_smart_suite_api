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
            'phone_one' => 'required|string',
            'employment_status' => 'required|string',
            'highest_qualification' => 'required|string',
            'field_of_study' => 'required|string',
            'years_experience' => 'required|integer',
            'salary' => 'required'
        ];
    }
}
