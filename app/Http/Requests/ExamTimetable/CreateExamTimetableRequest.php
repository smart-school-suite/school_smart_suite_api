<?php

namespace App\Http\Requests\ExamTimetable;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExamTimetableRule;
class CreateExamTimetableRequest extends FormRequest
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
            'entries' => ['required', 'array', new ExamTimetableRule($this->entries)],
            'entries.*.course_id' => 'required|exists:courses,id',
            'entries.*.exam_id' => 'required|exists:exams,id',
            'entries.*.student_batch_id' => 'required|exists:student_batch,id',
            'entries.*.specialty_id' => 'required|exists:specialty,id',
            'entries.*.school_year' => 'required|string',
            'entries.*.start_time' => 'required|date_format:H:i',
            'entries.*.level_id' => 'required|exists:education_levels,id',
            'entries.*.date' => 'required|date|after_or_equal:today',
            'entries.*.end_time' => 'required|date_format:H:i|after:entries.*.start_time',
            'entries.*.duration' => 'required|string'
        ];
    }
}
