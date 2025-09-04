<?php

namespace App\Http\Requests\ResitExamTimetable;
use App\Rules\ResitExamTimetableRule;

use Illuminate\Foundation\Http\FormRequest;

class CreateResitTimetableRequest extends FormRequest
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
           'entries' => ['required', 'array', new ResitExamTimetableRule($this->entries)],
            'entries.*.course_id' => 'required|exists:courses,id',
            'entries.*.resit_exam_id' => 'required|exists:resit_exams,id',
            //'entries.*.student_batch_id' => 'required|exists:student_batch,id',
            'entries.*.specialty_id' => 'required|exists:specialty,id',
            'entries.*.start_time' => 'required|date_format:H:i',
            'entries.*.level_id' => 'required|exists:education_levels,id',
            'entries.*.date' => 'required|date|after_or_equal:today',
            'entries.*.end_time' => 'required|date_format:H:i|after:entries.*.start_time',
        ];
    }
}
