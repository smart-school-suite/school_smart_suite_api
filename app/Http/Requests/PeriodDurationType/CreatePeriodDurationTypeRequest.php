<?php

namespace App\Http\Requests\PeriodDurationType;

use Illuminate\Foundation\Http\FormRequest;

class CreatePeriodDurationTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150|unique:period_duration_types,name',
            'description' => 'nullable|string|max:500', // Nullable since it can be empty in seeder
            'key' => 'required|string|max:100|regex:/^[a-z0-9_]+$/|unique:period_duration_types,key'
        ];
    }
}
