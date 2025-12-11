<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentMethodRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country_id' => 'required|string|exists:countries,id',
            'category_id' => 'required|string|exists:payment_method_category,id',
            'name' => 'required|string|max:150',
            'description' => 'required|string|max:500',
            'max_deposit' => 'required|numeric|min:0.01|max:9999999.99',
            'max_withdraw' => 'required|numeric|min:0.01|max:9999999.99',
            'operator_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }
}
