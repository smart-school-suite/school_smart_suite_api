<?php

namespace App\Http\Requests\ActivationCodeType;

use Illuminate\Foundation\Http\FormRequest;

class CreateActivationCodeTypeRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0.01|max:9999999.99',
            'description' => 'required|string|max:500',
            'type' => 'required|string|in:teacher,student',
            'country_id' => 'required|string|exists:countries,id'
        ];
    }
}
