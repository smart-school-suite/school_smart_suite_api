<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkPayAdditionFeeRequest extends FormRequest
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
           'additional_fee.*.amount' => 'required|integer',
           'additional_fee.*.fee_id' => 'required|string|exists:additional_fees,id',
           'additional_fee.*.payment_method' => 'required|string',
        ];
    }
}
