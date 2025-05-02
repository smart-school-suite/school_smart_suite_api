<?php

namespace App\Http\Requests;

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
            'school_year_start' => 'required|integer',
            'school_year_end' => 'required|integer',
            'semester_id' => 'required|integer|exists:semesters,id',
            'specialty_id' => 'required|integer|exists:specialties,id',
        ];
    }
}
