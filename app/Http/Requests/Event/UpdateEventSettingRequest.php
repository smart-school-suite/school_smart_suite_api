<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventSettingRequest extends FormRequest
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
            'value' => 'nullable|string|required_without_all:enabled',
            'enabled' => 'nullable|boolean|required_without_all:value',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'value.required_without_all' => 'Either the value or enabled field is required.',
            'enabled.required_without_all' => 'Either the value or enabled field is required.',
        ];
    }
}
