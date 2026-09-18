<?php

namespace App\Http\Requests\ExamEvaluation;

use Illuminate\Foundation\Http\FormRequest;

class CreateExamScoreRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "candidate_id" => "required|uuid|exists:exam_candidates,id",
            "scores" => "required|array|min:1",
            "scores.*.course_id" => "required|uuid|exists:courses,id",
            "scores.*.score" => [
                'required',
                'numeric',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'min:0',
                'max:999.99'
            ]
        ];
    }
}
