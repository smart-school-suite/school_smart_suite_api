<?php

namespace App\Http\Requests\AdditionalFee;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateAdditionalFeeRequest extends FormRequest
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
            'additional_fee' => 'required|array',
            'additional_fee.*.reason' => 'required|string',
            'additional_fee.*.amount' =>[
                'sometimes',
                'nullable',
                'numeric',
                'regex:/^\d{1,6}(\.\d{1,2})?$/',
                'min:0',
            ],,
            'additional_fee.*.specialty_id' => 'required|string|exists:specialty,id',
            'additional_fee.*.level_id' => 'required|string|exists:education_levels,id',
            'additional_fee.*.additionalfee_category_id' => 'required|string|exists:additional_fee_category,id',
            'additional_fee.*.student_id' => 'required|string|exists:student,id'
        ];
    }
}
