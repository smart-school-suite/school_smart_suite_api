<?php

namespace App\Http\Requests\AdditionalFee;

use Illuminate\Foundation\Http\FormRequest;

class AdditionalFeeTransactionIdRequest extends FormRequest
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
             'transactionIds' => 'required|array',
             'transactionIds.*transaction_id' => 'required|string|exists:additional_fee_transactions,id',
        ];
    }
}
