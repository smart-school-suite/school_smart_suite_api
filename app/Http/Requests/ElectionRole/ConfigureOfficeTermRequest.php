<?php

namespace App\Http\Requests\ElectionRole;

use Illuminate\Foundation\Http\FormRequest;

class ConfigureOfficeTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'duration_academic_years' => 'required|integer|min:1|max:10',
            'allow_reelection' => 'required|boolean',
            'maximum_terms' => 'nullable|integer|min:1|max:20',
        ];
    }
}
