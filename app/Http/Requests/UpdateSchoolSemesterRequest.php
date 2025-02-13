<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolSemesterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
   // {
      //  return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'start_date' => 'sometimes|required|date|after_or_equal:start_time',
            'end_date' => 'sometimes|required|date|after:start_date',
            'school_year_start' => 'sometimes|required|integer',
            'school_year_end' => 'sometimes|required|integer',
            'semester_id' => 'sometimes|required|integer|exists:semesters,id',
            'specialty_id' => 'sometimes|required|integer|exists:specialties,id',
            'school_branch_id' => 'sometimes|required|integer|exists:school_branches,id',
        ];
    }
}
