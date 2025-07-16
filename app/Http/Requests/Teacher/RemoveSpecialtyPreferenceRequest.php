<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class RemoveSpecialtyPreferenceRequest extends FormRequest
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
            'specialty_preferences' => ['required', 'array'],
            'specialty_preferences.*.preference_id' => 'exists:teacher_specailty_preference,id',
            'specialty_preferences.*.teacher_id' => 'exists:teacher,id'
        ];
    }
}
