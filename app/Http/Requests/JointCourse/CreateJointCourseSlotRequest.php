<?php

namespace App\Http\Requests\JointCourse;

use Illuminate\Foundation\Http\FormRequest;

class CreateJointCourseSlotRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "semester_joint_course_id" => "required|exists:semester_joint_courses,id",
            "slots" => "required|array|min:1",
            "slots.*.day" => "required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday",
            "slots.*.start_time" => "required|date_format:H:i",
            "slots.*.end_time" => "required|date_format:H:i|after:slots.*.start_time",
            "slots.*.hall_id" => "required|string|exists:halls,id",
        ];
    }
}
