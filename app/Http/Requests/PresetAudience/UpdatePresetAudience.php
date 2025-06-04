<?php

namespace App\Http\Requests\PresetAudience;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePresetAudience extends FormRequest
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
            'name' => 'sometimes|nullable|string|max:255',
            'target' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
        ];
    }
}
