<?php

namespace App\Http\Requests\SpecialtyTimetable;

use Illuminate\Foundation\Http\FormRequest;

class GenerateTimetableRequest extends FormRequest
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
            "specailty_id" => "required|string|exists:specialty,id",
            "semester_id" => "required|string|exists:school_semesters,id",
            "level_id" => "required|string|exists:education_levels,id",
            "student_batch_id" => "required|string|exists:student_batch,id"
        ];
    }
}
