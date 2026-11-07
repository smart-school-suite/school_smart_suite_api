<?php

namespace App\Http\Requests\ExamJointCourse;

use Illuminate\Foundation\Http\FormRequest;

class SaveExamJointCourseSlotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ];
    }
}
