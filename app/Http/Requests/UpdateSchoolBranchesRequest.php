<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolBranchesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'sometimes|required|string',
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
