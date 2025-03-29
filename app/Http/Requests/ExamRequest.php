<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
   // {
     //   return false;
 //   }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'exam_type_id' => 'required|string|exists:exam_type,id',
            'level_id' => 'string|required|exists:education_levels,id',
            'weighted_mark' => 'required',
            'semester_id' => 'required|string|exists:semesters,id',
            'school_year' => 'required|string',
            'specialty_id' => 'required|string|exists:specialty,id',
            'student_batch_id' => 'required|string|exists:student_batch,id'
        ];
    }
}
