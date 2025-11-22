<?php

namespace App\Http\Requests\SchoolSemester;

use Illuminate\Foundation\Http\FormRequest;

class CreateSchoolSemesterRequest extends FormRequest
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
            'start_date' => 'required|date|after_or_equal:start_time',
            'end_date' => 'required|date|after:start_date',
            'school_year' => 'required|string',
            'semester_id' => 'required|string|exists:semesters,id',
            'specialty_id' => 'required|string|exists:specialties,id',
            'student_batch_id' => 'required|string|exists:student_batches,id'
        ];
    }
}
