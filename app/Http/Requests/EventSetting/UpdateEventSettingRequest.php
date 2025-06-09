<?php

namespace App\Http\Requests\EventSetting;

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
           'title' => 'sometimes|nullable|string|max:150',
           'description' => 'sometimes|nullable|string|max:5000'
        ];
    }
}
