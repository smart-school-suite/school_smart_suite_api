<?php

namespace App\Http\Requests\ResitExam;

use Illuminate\Foundation\Http\FormRequest;

class ResitExamIdRequest extends FormRequest
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
            'resitExamIds' => "required|array",
            "resitExamIds.*.resit_exam_id" => 'required|exists:resit_exams,id'
        ];
    }
}
