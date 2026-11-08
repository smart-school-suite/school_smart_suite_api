<?php

namespace App\Http\Requests\ExamJointCourse;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmExamJointCourseSlotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // A joint exam takes place once: one option from the generated times.
            'option_id' => 'required|string',
        ];
    }
}
