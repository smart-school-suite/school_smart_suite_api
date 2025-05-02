<?php

namespace App\Http\Requests\ResitExamScore;

use Illuminate\Foundation\Http\FormRequest;

class CreateResitExamScore extends FormRequest
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
            'entries.*.student_id' => 'required|string',
            'entries.*.course_id' => 'required|string',
            'entries.*.specialty_id' => 'required|string',
            'entries.*.exam_id' => 'required|string',
            'entries.*.score' => [
                'required',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
        ];
    }
}
