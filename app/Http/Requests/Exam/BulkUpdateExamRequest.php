<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateExamRequest extends FormRequest
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
            'exams' => 'required|array|min:1',
            'exams.*.exam_id' => 'required|string|distinct|exists:exams,id',
            'exams.*.start_date' => 'sometimes|nullable|date',
            'exams.*.end_date' => 'sometimes|nullable|date',
            'exams.*.exam_type_id' => 'sometimes|nullable|string|exists:exam_types,id',
            'exams.*.max_score' =>  [
                'sometimes',
                'nullable',
                'numeric',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'min:0',
                'max:999.99'
            ],
            'exams.*.school_year_id' => 'sometimes|nullable|string|exists:school_academic_years,id',
        ];
    }
}
