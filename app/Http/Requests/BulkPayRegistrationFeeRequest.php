<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkPayRegistrationFeeRequest extends FormRequest
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
            'registration_fee' => 'required|array',
            'registration_fee.*.id' => 'required|string|exists:registration_fees,id',
            'registration_fee.*.amount' => 'required|integer',
            'registration_fee.*.registration_fee_id' => 'required|string|exists:registration_fees,id',
            'registration_fee.*.payment_method' => 'required|string',
        ];
    }
}
