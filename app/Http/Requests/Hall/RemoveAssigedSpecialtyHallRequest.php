<?php

namespace App\Http\Requests\Hall;

use Illuminate\Foundation\Http\FormRequest;

class RemoveAssigedSpecialtyHallRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hallIds' =>  "required|array",
            "hallIds.*.hall_id" => "required|string|exists:halls,id",
            "specialty_id" => "required|string|exists:specialties,id"
        ];
    }
}
