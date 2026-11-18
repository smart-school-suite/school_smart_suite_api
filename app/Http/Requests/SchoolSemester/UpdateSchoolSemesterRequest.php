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
    /**
     * Shape only. Whether each field may change, and within which dates, is
     * decided by SchoolSemesterUpdatePolicy.
     */
    public function rules()
    {
        return [
            'start_date' => 'sometimes|nullable|date_format:Y-m-d',
            'end_date' => 'sometimes|nullable|date_format:Y-m-d',
            'school_year_id' => 'sometimes|nullable|string',
            'semester_id' => 'sometimes|nullable|string|exists:semesters,id',
        ];
    }
}
