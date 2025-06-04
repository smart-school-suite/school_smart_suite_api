<?php

namespace App\Http\Requests\PresetAudience;

use Illuminate\Foundation\Http\FormRequest;

class CreatePresetAudience extends FormRequest
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
            'name' => 'required|string|max:255',
            'target' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
