<?php

namespace App\Http\Requests\ExamInvigilator;

use Illuminate\Foundation\Http\FormRequest;

class AssignInvigilatorRequest extends FormRequest
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
            "exam_id" => ["required", "exists:exams,id"],
            "invigilatorIds" => ["required", "array", "min:1"],
            "invigilatorIds.*" => ["required", "uuid", "exists:invigilators,id"]
        ];
    }
}
