<?php

namespace App\Http\Requests\SemesterTimetable;

use Illuminate\Foundation\Http\FormRequest;

class GetCourseRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
          "school_semester_id" => ["required", "exists:school_semesters,id"],
          "teacher_id" => ["nullable", "sometimes",  "exists:teachers,id"]
        ];
    }
}
