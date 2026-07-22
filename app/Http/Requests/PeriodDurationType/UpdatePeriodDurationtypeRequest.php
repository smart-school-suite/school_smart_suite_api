<?php

namespace App\Http\Requests\PeriodDurationType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdatePeriodDurationtypeRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique('period_duration_types', 'name')
                    ->ignore($this->route('periodDurationType')) // Assuming route parameter name
            ],
            'description' => 'nullable|string|max:500',
            'key' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('period_duration_types', 'key')
                    ->ignore($this->route('periodDurationType'))
            ]
        ];
    }
}
