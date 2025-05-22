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
            'branch_name' => 'sometimes|required|string',
            'address' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'state' => 'sometimes|required|string',
            'postal_code' => 'sometimes|required|string',
            'website' => 'sometimes|string',
            'phone_one' => 'sometimes|required|string',
            'phone_two' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|string',
            'max_gpa' => 'sometimes|required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'semester_count' => 'sometimes|required|integer'
        ];
    }
}
