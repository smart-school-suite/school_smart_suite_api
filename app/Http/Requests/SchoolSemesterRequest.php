<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolSemesterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /// public function authorize(): bool
    //{
    // //    return false;
    //}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required|date|after_or_equal:start_time',
            'end_date' => 'required|date|after:start_date',
            'school_year_start' => 'required|integer',
            'school_year_end' => 'required|integer',
            'semester_id' => 'required|string|exists:semesters,id',
            'specialty_id' => 'required|string|exists:specialty,id',
            'student_batch_id' => 'required|string|exists:student_batch,id'
        ];
    }
}
