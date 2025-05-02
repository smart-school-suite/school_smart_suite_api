<?php

namespace App\Http\Requests\ExamScore;

use Illuminate\Foundation\Http\FormRequest;

class CreateExamScoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'scores_entries' => 'required|array',
            'scores_entries.*.student_id' => 'required|string|exists:student,id',
            'scores_entries.*.score' => [
                'required',
                'numeric',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'min:0',
                'max:999.99'
            ],
            'scores_entries.*.exam_id' => 'required|string|exists:exams,id'
        ];
    }
}
