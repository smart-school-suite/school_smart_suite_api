<?php

namespace App\Http\Requests\Teacher;

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
            'first_name' => 'sometimes|required|string|max:50',
            'last_name' => 'sometimes|required|string|max:50',
            'name' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|email|string|max:100',
            'address' => 'sometimes|nullable|string|max:200',
            'gender_id' => 'sometimes|required|string|exists:genders,id',
            'phone' => 'sometimes|nullable|string|max:20',
            'allowed_level_ids' => 'sometimes|required|array|min:1',
            'allowed_level_ids.*' => 'required|uuid|exists:levels,id',
            'qualifications' => 'sometimes|required|array|min:1',
            'qualifications.*.qualification_id' => 'required|uuid|distinct|exists:qualifications,id',
            'qualifications.*.field_of_study' => 'required|string|max:200',
            'qualifications.*.institution' => 'required|string|max:150',
            'qualifications.*.year' => 'required|string|max:50'
        ];
    }
}
