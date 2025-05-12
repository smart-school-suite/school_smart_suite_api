<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateResitExamRequest extends FormRequest
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
            'exams' => 'required|array',
            'exams.*.resit_exam_id' => 'required|string|exists:resit_exams,id',
            'exams.*.start_date' => 'sometimes|nullable|date',
            'exams.*.end_date' => 'sometimes|nullable|date',
            'exams.*.weighted_mark' =>  [
                'sometimes',
                'nullable',
                'numeric',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'min:0',
                'max:999.99'
            ],
        ];
    }
}
