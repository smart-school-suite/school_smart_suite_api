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
            'exams' => 'required|array',
            'exams.*.exam_id' => 'required|string|exists:exams,id',
            'exams.*.start_date' => 'sometimes|nullable|date',
            'exams.*.end_date' => 'sometimes|nullable|date',
            'exams.*.exam_type_id' => 'sometimes|nullable|string|exists:exam_type,id',
            'exams.*.weighted_mark' =>  [
                'sometimes',
                'nullable',
                'numeric',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'min:0',
                'max:999.99'
            ],
            'exams.*.school_year' => 'sometimes|nullable|string',
        ];
    }
}
