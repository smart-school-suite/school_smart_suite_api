<?php

namespace App\Http\Requests\StudentResit;

use Illuminate\Foundation\Http\FormRequest;

class PayResitFeeRequest extends FormRequest
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
            'student_resit_id' => 'required|string|exists:student_resit,id',
            'payment_method' => 'required|string',
            'amount' => 'required'
        ];
    }
}
