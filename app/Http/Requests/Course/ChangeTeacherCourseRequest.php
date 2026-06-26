<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class ChangeTeacherCourseRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'course_id' => 'required|uuid|exists:courses,id',
           'new_teacher_id' => 'required|uuid|exists:teachers,id',
           'old_teacher_id' => 'required|uuid|exists:teachers,id'
        ];
    }
}
