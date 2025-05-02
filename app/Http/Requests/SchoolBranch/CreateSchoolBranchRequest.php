<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSchoolBranchRequest extends FormRequest
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
            'school_id' => 'required|string',
            'branch_name' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'website' => 'string',
            'phone_one' => 'required|string',
            'phone_two' => 'required|string',
            'email' => 'required|email|string',
            'max_gpa' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'semester_count' => 'required|integer',
            'abbrevaition' => 'required|string'
        ];
    }
}
