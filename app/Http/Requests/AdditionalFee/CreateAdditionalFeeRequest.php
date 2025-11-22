<?php

namespace App\Http\Requests\AdditionalFee;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdditionalFeeRequest extends FormRequest
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
           'reason' => 'required|string',
           'amount' => [
                'required',
                'numeric',
                'regex:/^\d{1,6}(\.\d{1,2})?$/',
                'min:0',
            ],
           'additionalfee_category_id' => 'required|string|exists:additional_fee_categories,id',
           'student_id' => 'required|string|exists:student,id'
        ];
    }
}
