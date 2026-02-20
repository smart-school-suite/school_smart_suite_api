<?php

namespace App\Http\Requests\JointCourse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJointCourseSlotRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "semester_joint_course_id" => "required|integer|exists:semester_joint_courses,id",
            "slots" => "required|array|min:1",
            'slots.*.slot_id' => 'required|integer|exists:joint_course_slots,id',
            "slots.*.day" => "required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday",
            "slots.*.start_time" => "required|date_format:H:i",
            "slots.*.end_time" => "required|date_format:H:i|after:slots.*.start_time",
            "slots.*.hall_id" => "required|integer|exists:halls,id",
            "slots.*.teacher_id" => "required|integer|exists:teachers,id",
        ];
    }
}
