<?php

namespace App\Http\Requests\SchoolBranch;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolBranchRequest extends FormRequest
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
            'name' => 'sometimes|nullable|string',
            'address' => 'sometimes|nullable|string',
            'city' => 'sometimes|nullable|string',
            'state' => 'sometimes|nullable|string',
            'postal_code' => 'sometimes|nullable|string',
            'website' => 'sometimes|string',
            'phone_one' => 'sometimes|nullable|string',
            'phone_two' => 'sometimes|nullable|string',
            'email' => 'sometimes|nullable|email|string',
            'abbreviation' => 'sometimes|nullable|string',
            'max_gpa' => 'sometimes|nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'semester_count' => 'sometimes|nullable|integer'
        ];
    }
}
