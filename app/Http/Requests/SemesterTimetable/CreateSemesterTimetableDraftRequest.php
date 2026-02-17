<?php

namespace App\Http\Requests\SemesterTimetable;

use Illuminate\Foundation\Http\FormRequest;

class CreateSemesterTimetableDraftRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "school_semester_id" => "required|string|exists:school_semesters,id",
        ];
    }
}
