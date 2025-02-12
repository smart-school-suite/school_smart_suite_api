<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  //  public function authorize(): bool
   // {
 //   //   return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'fee_name' => 'sometimes|string',
           'amount' => 'sometimes',
           'student_id' => 'sometimes|string'
        ];
    }
}
