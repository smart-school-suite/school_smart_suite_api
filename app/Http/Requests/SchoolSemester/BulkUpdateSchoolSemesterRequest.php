<?php

namespace App\Http\Requests\SchoolSemester;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateSchoolSemesterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'school_semester' => 'required|array',
            'school_semester.*.school_semester_id' => 'required|string|exists:school_semesters,id',
            'school_semester.*.start_date' => 'sometimes|required|date|after_or_equal:start_time',
            'school_semester.*.end_date' => 'sometimes|required|date|after:start_date',
            'school_semester.*.school_year_start' => 'sometimes|required|integer',
            'school_semester.*.school_year_end' => 'sometimes|required|integer',
            'school_semester.*.semester_id' => 'sometimes|required|integer|exists:semesters,id',
            'school_semester.*.specialty_id' => 'sometimes|required|integer|exists:specialties,id',
        ];
    }
}
