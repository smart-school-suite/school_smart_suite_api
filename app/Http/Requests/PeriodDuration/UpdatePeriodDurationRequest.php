<?php

namespace App\Http\Requests\PeriodDuration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdatePeriodDurationRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:150',
            'minutes' => 'sometimes|required|integer|min:1|max:1440',
            'description' => 'sometimes|required|string|max:500',
            'key' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('period_durations', 'key')
                    ->where('type_id', $this->type_id ?? $this->route('periodDuration')->type_id)
                    ->ignore($this->route('periodDuration'))
            ],
            'type_id' => 'sometimes|required|uuid|exists:period_duration_types,id'
        ];
    }
}
