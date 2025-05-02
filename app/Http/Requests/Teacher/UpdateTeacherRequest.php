<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Update this as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'sometimes|nullable|string',
            'last_name' => 'sometimes|nullable|string',
            'name' => 'sometimes|nullable|string',
            'email' => 'sometimes|nullable|email',
            'hire_date' => 'sometimes|nullable|date',
            'highest_qualification' => 'sometimes|nullable|string',
            'field_of_study' => 'sometimes|nullable|string',
            'years_experience' => 'sometimes|nullable|integer|max:50|min:0'
        ];
    }
}
