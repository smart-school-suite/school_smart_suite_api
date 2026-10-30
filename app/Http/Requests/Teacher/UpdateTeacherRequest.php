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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'sometimes|string|max:50',
            'last_name' => 'sometimes|string|max:50',
            'name' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|string|max:100',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:200',
            'gender_id' => 'sometimes|string|exists:genders,id',
            'allowed_level_ids' => 'sometimes|array|min:1',
            'allowed_level_ids.*' => 'required_with:allowed_level_ids|uuid|exists:levels,id',
            'qualifications' => 'sometimes|array|min:1',
            'qualifications.*.qualification_id' => 'required_with:qualifications|uuid|exists:qualifications,id',
            'qualifications.*.field_of_study' => 'required_with:qualifications|string|max:200',
            'qualifications.*.institution' => 'required_with:qualifications|string|max:150',
            'qualifications.*.year' => 'required_with:qualifications|string|max:50',
        ];
    }
}
