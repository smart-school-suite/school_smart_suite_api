<?php

namespace App\Http\Requests\SchoolAcademicYear;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolAcademicYearRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'system_academic_year_id' => 'sometimes|nullable|uuid|exists:system_academic_years,id',
            'specialty_id' => 'sometimes|nullable|uuid|exists:specialties,id',
            'start_date' => 'sometimes|nullable|date|after_or_equal:today',
            'end_date' => 'sometimes|nullable|date|after:start_date',
        ];
    }
}
