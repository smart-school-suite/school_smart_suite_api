<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamTimeTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
    // {
    // return false;
    //  }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exam_courses' => 'required|array',
            'exam_courses.*.course_id' => 'required|exists:courses,id',
            'exam_courses.*.exam_id' => 'required|exists:exams,id',
            'exam_courses.*.student_batch_id' => 'required|exists:student_batch,id',
            'exam_courses.*.specialty_id' => 'required|exists:specialty,id',
            'exam_courses.*.start_time' => 'required|date_format:H:i:s',
            'exam_courses.*.level_id' => 'required|exists:education_levels,id',
            'exam_courses.*.day' => 'required|date|after_or_equal:today',
            'exam_courses.*.end_time' => 'required|date_format:H:i:s|after:exam_courses.*.start_time',
        ];
    }
}
