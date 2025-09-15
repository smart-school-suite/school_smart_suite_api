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
            'additional_fee.*.fee_id' => 'required|string|exists:additional_fees,id',
            'additional_fee.*.reason' => 'sometimes|nullable|string|max:500',
            'additional_fee.*.amount' =>[
                'sometimes',
                'nullable',
                'numeric',
                'regex:/^\d{1,6}(\.\d{1,2})?$/',
                'min:0',
            ],
            'additional_fee.*.additionalfee_category_id' => 'sometimes|nullable|string|exists:additional_fee_category,id'
        ];
    }
}
