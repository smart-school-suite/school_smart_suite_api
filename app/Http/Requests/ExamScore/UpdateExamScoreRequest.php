<?php

namespace App\Http\Requests\ExamScore;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamScoreRequest extends FormRequest
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
            'scores' => 'required|array',
            'scores.*.score_id' => 'required|string|exists:exam_scores,id',
            'scores.*.course_id' => 'required|string|exists:courses,id',
            'scores.*.score' => [
                'sometimes',
                'nullable',
                'numeric',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'min:0',
                'max:999.99'
            ]
        ];
    }
}
