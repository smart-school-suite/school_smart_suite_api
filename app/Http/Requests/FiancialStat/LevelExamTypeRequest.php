<?php

namespace App\Http\Requests\FiancialStat;

use Illuminate\Foundation\Http\FormRequest;

class LevelExamTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "level" => "sometimes|nullable|boolean",
            "exam_type" => "sometimes|nullable|boolean"
        ];
    }
}
