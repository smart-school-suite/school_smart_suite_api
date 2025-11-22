<?php

namespace App\Http\Requests\ResitExamScore;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResitExamScore extends FormRequest
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
            'entries' => 'required|array',
            'entries.*.student_id' => 'required|string|exists:students,id',
            'entries.*.resit_exam_id' => 'required|string|exists:resit_exams,id',
            'entries.*.resit_mark_id' => 'required|string|exists:resit_marks,id',
            'entries.*.course_id' => 'required|string|exists:courses,id',
            'entries.*.specialty_id' => 'required|string|exists:specialties,id',
            'entries.*.exam_id' => 'required|string|exists:exams,id',//exam the student failed
            'entries.*.score' => [
                'required',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
        ];
    }
}
