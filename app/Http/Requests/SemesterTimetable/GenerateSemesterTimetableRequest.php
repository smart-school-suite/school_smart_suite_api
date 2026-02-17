<?php

namespace App\Http\Requests\SemesterTimetable;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSemesterTimetableRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            "school_semester_id" => "required|string|exists:school_semesters,id",
            "draft_id" => "sometimes|nullable|string|exists:timetable_drafts,id",
            'prompt_id' => 'sometimes|nullable|string|exists:timetable_prompts,id',
            'parent_version_id' => 'sometimes|nullable|string|exists:timetable_versions,id',
        ];
    }
}
