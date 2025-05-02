<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
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
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date',
            'exam_type_id' => 'sometimes|nullable|string|exists:exam_type,id',
            'level_id' => 'sometimes|nullable|string|exists:education_levels,id',
            'weighted_mark' => [
                'sometimes',
                'nullable',
                'numeric',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'min:0',
                'max:999.99'
            ],
            'semester_id' => 'sometimes|nullable|string|exists:semesters,id',
            'school_year' => 'sometimes|nullable|string',
            'specialty_id' => 'sometimes|nullable|string|exists:specialty,id',
        ];
    }
}
