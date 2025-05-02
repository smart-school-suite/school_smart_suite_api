<?php

namespace App\Http\Requests\StudentBatch;

use Illuminate\Foundation\Http\FormRequest;

class BulkAddGraduationDateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'grad_dates' => 'required|array',
            'grad_dates.*.graduation_date' => 'required|date',
            'grad_dates.*.specialty_id' => 'required|string|exists:specialty,id',
            'grad_dates.*.level_id' => 'required|string|exists:education_levels,id',
            'grad_dates.*.student_batch_id' => 'required|string|exists:student_batch,id'
        ];
    }
}
