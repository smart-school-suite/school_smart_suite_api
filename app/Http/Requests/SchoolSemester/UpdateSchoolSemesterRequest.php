<?php

namespace App\Http\Requests\SchoolSemester;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolSemesterRequest extends FormRequest
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
            'start_date' => 'sometimes|nullable|date|after_or_equal:start_time',
            'end_date' => 'sometimes|nullable|date|after:start_date',
            'school_year' => 'sometimes|nullable|string',
            'semester_id' => 'sometimes|nullable|string|exists:semesters,id',
            'specialty_id' => 'sometimes|nullable|string|exists:specialty,id',
            'student_batch_id' => 'sometimes|nullable|exists:student_batch,id'
        ];
    }
}
