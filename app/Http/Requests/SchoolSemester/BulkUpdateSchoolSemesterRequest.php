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
            'school_semester.*.start_date' => 'sometimes|nullable|date|after_or_equal:start_time',
            'school_semester.*.end_date' => 'sometimes|nullable|date|after:start_date',
            'school_semester.*.school_year' => 'sometimes|nullable|string',
            'school_semester.*.semester_id' => 'sometimes|nullable|string|exists:semesters,id',
            //'school_semester.*.student_batch_id' => 'sometimes|nullable|string|exists:student_batch,id',
            //'school_semester.*.specialty_id' => 'sometimes|nullable|string|exists:specialties,id',
        ];
    }
}
