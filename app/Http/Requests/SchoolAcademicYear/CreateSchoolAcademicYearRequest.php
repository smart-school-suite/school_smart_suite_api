<?php

namespace App\Http\Requests\SchoolAcademicYear;

use Illuminate\Foundation\Http\FormRequest;

class CreateSchoolAcademicYearRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'system_academic_year_id' => 'required|uuid|exists:system_academic_years,id',
            'specialty_id' => 'required|uuid|exists:specialties,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ];
    }
}
