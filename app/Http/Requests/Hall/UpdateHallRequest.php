<?php

namespace App\Http\Requests\Hall;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHallRequest extends FormRequest
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
            'name' => 'nullable|string|max:150',
            'capacity' => 'nullable|integer|max:5000',
            'location' => 'nullable|string|max:200',
            'typeIds' => "sometimes|nullable|array",
            "typeIds.*.type_id" => "sometimes|nullable|string|exists:hall_types,id"
        ];
    }
}
