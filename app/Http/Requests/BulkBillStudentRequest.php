<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkBillStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'additional_fee' => 'required|array',
            'additional_fee.*.reason' => 'required|string',
            'additional_fee.*.amount' => 'required|integer',
            'additional_fee.*.specialty_id' => 'required|string|exists:specialty,id',
            'additional_fee.*.level_id' => 'required|string|exists:education_levels,id',
            'additional_fee.*.additionalfee_category_id' => 'required|string|exists:additional_fee_category,id',
            'additional_fee.*.student_id' => 'required|string|exists:student,id'
        ];
    }
}
