<?php

namespace App\Http\Requests\Invigilator;

use Illuminate\Foundation\Http\FormRequest;

class RemoveInvigilatorRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exam_id' => 'required|uuid|exists:exams,id',
            "invigilator_ids" => ["required", "array", "min:1"],
            "invigilator_ids.*" => ["required", "uuid", "exists:exam_invigilators,id"]
        ];
    }
}
