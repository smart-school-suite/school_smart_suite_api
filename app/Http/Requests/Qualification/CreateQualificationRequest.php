<?php

namespace App\Http\Requests\Qualification;

use Illuminate\Foundation\Http\FormRequest;

class CreateQualificationRequest extends FormRequest
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
            'abbreviation' => 'required|char|max:10',
            'level' => 'required|string|max:150',
        ];
    }
}
