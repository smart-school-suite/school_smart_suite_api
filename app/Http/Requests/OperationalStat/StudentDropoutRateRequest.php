<?php

namespace App\Http\Requests\OperationalStat;

use Illuminate\Foundation\Http\FormRequest;

class StudentDropoutRateRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "level_id" => "sometimes|nullable|string|exists:levels,id",
            "gender_id" => "sometimes|nullable|string|exists:genders,id"
        ];
    }
}
