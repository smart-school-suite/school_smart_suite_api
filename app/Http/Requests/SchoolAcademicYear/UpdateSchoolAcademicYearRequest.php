<?php

namespace App\Http\Requests\SchoolAcademicYear;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolAcademicYearRequest extends FormRequest
{

    /**
     * Shape only. Whether each field may change, and within which dates, is
     * decided by SchoolAcademicYearUpdatePolicy.
     */
    public function rules(): array
    {
        return [
            'system_academic_year_id' => 'sometimes|nullable|string|exists:system_academic_years,id',
            'specialty_id' => 'sometimes|nullable|string',
            'start_date' => 'sometimes|nullable|date_format:Y-m-d',
            'end_date' => 'sometimes|nullable|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'system_academic_year_id.exists' => 'The selected academic year does not exist.',
            'start_date.date_format' => 'The start date must be a valid date (YYYY-MM-DD).',
            'end_date.date_format' => 'The end date must be a valid date (YYYY-MM-DD).',
        ];
    }
}
