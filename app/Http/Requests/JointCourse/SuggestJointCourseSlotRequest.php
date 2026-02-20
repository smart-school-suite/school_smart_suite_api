<?php

namespace App\Http\Requests\JointCourse;

use Illuminate\Foundation\Http\FormRequest;

class SuggestJointCourseSlotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
                'semester_joint_course_id' => 'required|exists:semester_joint_courses,id',
                'interval' => "nullable|in:30,60,45,120",
                'hall_id' => "nullable|exists:halls,id",
        ];
    }
}
