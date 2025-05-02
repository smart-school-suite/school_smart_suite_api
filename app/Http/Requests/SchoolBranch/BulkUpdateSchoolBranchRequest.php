<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateSchoolBranchRequest extends FormRequest
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
            'school_branch' => 'required|array',
            'school_branch.*.id' => 'required|string|exists:school_branches,id',
            'school_branch.*.school_id' => 'sometimes|required|string',
            'school_branch.*.branch_name' => 'sometimes|required|string',
            'school_branch.*.address' => 'sometimes|required|string',
            'school_branch.*.city' => 'sometimes|required|string',
            'school_branch.*.state' => 'sometimes|required|string',
            'school_branch.*.postal_code' => 'sometimes|required|string',
            'school_branch.*.website' => 'sometimes|string',
            'school_branch.*.phone_one' => 'sometimes|required|string',
            'school_branch.*.phone_two' => 'sometimes|required|string',
            'school_branch.*.email' => 'sometimes|required|email|string',
            'school_branch.*.max_gpa' => 'sometimes|required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'school_branch.*.semester_count' => 'sometimes|required|integer'
        ];
    }
}
