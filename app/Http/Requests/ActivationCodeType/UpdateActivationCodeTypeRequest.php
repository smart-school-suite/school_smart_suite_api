<?php

namespace App\Http\Requests\ActivationCodeType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivationCodeTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|sometimes|string|max:150',
            'price' => 'nullable|sometimes|numeric|min:0.01|max:9999999.99',
            'description' => 'nullable|sometimes|string|max:500',
            'type' => 'nullable|sometimes|string|in:teacher,student',
            'country_id' => 'nullable|sometimes|string|exists:countries,id'
        ];
    }
}
