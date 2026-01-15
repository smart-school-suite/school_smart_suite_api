<?php

namespace App\Http\Requests\HallType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHallTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => "sometimes|nullable|string|max:150",
            "key" => "sometimes|nullable|string|max:100",
            "description" => "sometimes|nullable|string|max:500",
            "text_color" => "sometimes|nullable|string|max:7",
            "background_color" => "sometimes|nullable|string|max:7"
        ];
    }
}
