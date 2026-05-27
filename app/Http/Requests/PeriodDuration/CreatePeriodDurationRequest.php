<?php

namespace App\Http\Requests\PeriodDuration;

use Illuminate\Foundation\Http\FormRequest;

class CreatePeriodDurationRequest extends FormRequest
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
            'minutes' => 'required|integer|min:1|max:1440',
            'description' => 'required|string|max:500',
            'key' => 'required|string|max:100|regex:/^[a-z0-9_]+$/',
            'type_id' => 'required|uuid|exists:period_duration_types,id'
        ];
    }
}
