<?php

namespace App\Http\Requests\Hall;

use Illuminate\Foundation\Http\FormRequest;

class ValidateHallIdRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "hallIds" => "required|array|min:1",
            "hallIds.*.hall_id" => "required|string|distinct|exists:halls,id",
        ];
    }
}
