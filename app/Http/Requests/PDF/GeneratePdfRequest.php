<?php

namespace App\Http\Requests\PDF;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePdfRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string'],
            'title' => ['nullable', 'string'],
            'filters' => ['nullable', 'array'],
            'options' => ['nullable', 'array'],
        ];
    }
}
