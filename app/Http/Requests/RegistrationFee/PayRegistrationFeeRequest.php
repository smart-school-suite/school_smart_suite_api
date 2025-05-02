<?php

namespace App\Http\Requests\RegistrationFee;

use Illuminate\Foundation\Http\FormRequest;

class PayRegistrationFeeRequest extends FormRequest
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
           'amount' => 'required|integer',
           'registration_fee_id' => 'required|string|exists:registration_fees,id',
           'payment_method' => 'required|string',
        ];
    }
}
