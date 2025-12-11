<?php

namespace App\Http\Requests\ActivationCode;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseActivationCodeRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => "required|string|exists:payment_method,id",
            "teacher_code_count" => "required|integer|max:1000,min:1",
            "student_code_count" => "required|integer|max:1000,min:1"
        ];
    }
}
