<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exams' => 'required|array',
            'exams.*.start_date' => 'sometimes|nullable|date',
            'exams.*.end_date' => 'sometimes|nullable|date',
            'exams.*.exam_type_id' => 'sometimes|nullable|string|exists:exam_type,id',
            'exams.*.level_id' => 'sometimes|nullable|string|exists:education_levels,id',
            'exams.*.weighted_mark' => 'sometimes|nullable',
            'exams.*.semester_id' => 'sometimes|nullable|string|exists:semesters,id',
            'exams.*.school_year' => 'sometimes|nullable|string',
            'exams.*.specialty_id' => 'sometimes|nullable|string|exists:specialty,id',
        ];
    }
}
