<?php

namespace App\Http\Requests\ExamGrading;

use Illuminate\Foundation\Http\FormRequest;

class BulkAddResitExamGradingRequest extends FormRequest
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
            'exam_grading' => 'required|array',
            'exam_grading.*.resit_exam_id' => 'required|exists:resit_exams,id',
            'exam_grading.*.grades_config_Id' => 'required|exists:school_grades_config,id'
        ];
    }
}
