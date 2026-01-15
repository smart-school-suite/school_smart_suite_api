<?php

namespace App\Http\Requests\Hall;

use Illuminate\Foundation\Http\FormRequest;

class CreateHallRequest extends FormRequest
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
            'capacity' => 'required|integer|max:5000',
            'location' => 'required|string|max:200',
            'typeIds' => "required|array",
            "typeIds.*.type_id" => "required|string|exists:hall_types,id"
        ];
    }
}
