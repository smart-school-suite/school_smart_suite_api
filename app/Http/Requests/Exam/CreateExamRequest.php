<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class CreateExamRequest extends FormRequest
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
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'exam_type_id' => 'required|string|exists:exam_type,id',
            'weighted_mark' => [
                'required',
                'numeric',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'min:0',
                'max:999.99'
            ],
            'school_year' => 'required|string',
            'specialty_id' => 'required|string|exists:specialty,id',
            'student_batch_id' => 'required|string|exists:student_batch,id'
        ];
    }
}
