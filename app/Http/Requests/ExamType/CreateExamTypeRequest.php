<?php

namespace App\Http\Requests\ExamType;

use Illuminate\Foundation\Http\FormRequest;

class CreateExamTypeRequest extends FormRequest
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
            'semester_id' => 'required|string|exists:semesters,id',
           'exam_name' => 'required|string',
           'program_name' => 'required|string',
        ];
    }
}
