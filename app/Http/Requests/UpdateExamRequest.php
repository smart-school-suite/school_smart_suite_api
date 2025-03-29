<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  //  public function authorize(): bool
 //   {
   //     return false;
  //  }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date',
            'exam_type_id' => 'sometimes|nullable|string|exists:exam_type,id',
            'level_id' => 'sometimes|nullable|string|exists:education_levels,id',
            'weighted_mark' => 'sometimes',
            'semester_id' => 'sometimes|nullable|string|exists:semesters,id',
            'school_year' => 'sometimes|nullable|string',
            'specialty_id' => 'sometimes|nullable|string|exists:specialty,id',
        ];
    }
}
