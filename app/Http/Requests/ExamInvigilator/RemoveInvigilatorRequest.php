<?php

namespace App\Http\Requests\ExamInvigilator;

use Illuminate\Foundation\Http\FormRequest;

class RemoveInvigilatorRequest extends FormRequest
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
            "exam_id" => ["required", "uuid", "exists:exams,id"],
            "exam_invigilator_ids" => ["required", "array", "min:1"],
            "exam_invigilator_ids.*" => ["required", "uuid", "exists:exam_invigs,id"]
        ];
    }
}
