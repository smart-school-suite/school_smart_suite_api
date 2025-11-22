<?php

namespace App\Http\Requests\ExamGrading;

use Illuminate\Foundation\Http\FormRequest;

class BulkAddExamGradingRequest extends FormRequest
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
            'exam_grading.*.exam_id' => 'required|string|exists:exams,id',
            'exam_grading.*.grades_config_Id' => 'required|string|exists:school_grade_scale_categories,id'
        ];
    }
}
