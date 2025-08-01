<?php

namespace App\Http\Requests\RegistrationFee;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationFeeTransactionIdRequest extends FormRequest
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
            'transactionIds.*.transaction_id' => 'required|string|exists:registration_fee_transactions,id',
        ];
    }
}
