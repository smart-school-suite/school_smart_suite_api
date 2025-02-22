<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayAdditionalFeesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
    //{
      //  return false;
    //}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'amount' => 'required|integer',
           'fee_id' => 'required|string|exists:additional_fees,id',
           'payment_method' => 'required|string',

        ];
    }
}
