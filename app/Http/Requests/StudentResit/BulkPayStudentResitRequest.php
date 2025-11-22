<?php

namespace App\Http\Requests\StudentResit;

use Illuminate\Foundation\Http\FormRequest;

class BulkPayStudentResitRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "paymentData" => 'required|array',
            "paymentData.*.resit_id" => 'required|string|exists:student_resits,id',
            'paymentData.*.amount' => 'required',
            'paymentData.*.payment_method' => 'required|string'
        ];
    }
}
