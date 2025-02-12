<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
   // {
     //   return false;
 //   }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required',
            'end_date' => 'required',
            'exam_type_id' => 'required|string',
            'level_id' => 'string|required',
            'weighted_mark' => 'required',
            'semester_id' => 'required|string',
            'school_year' => 'required|string',
            'specialty_id' => 'required|string'
        ];
    }
}
