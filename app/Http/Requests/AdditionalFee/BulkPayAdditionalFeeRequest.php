<?php

namespace App\Http\Requests\AdditionalFee;

use Illuminate\Foundation\Http\FormRequest;

class BulkPayAdditionalFeeRequest extends FormRequest
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
           'additional_fee.*.amount' =>  [
                'required',
                'numeric',
                'regex:/^\d{1,6}(\.\d{1,2})?$/',
                'min:0',
            ],
           'additional_fee.*.fee_id' => 'required|string|exists:additional_fees,id',
           'additional_fee.*.payment_method' => 'required|string',
        ];
    }
}
