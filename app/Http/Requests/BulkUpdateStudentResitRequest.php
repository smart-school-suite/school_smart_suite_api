<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateStudentResitRequest extends FormRequest
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
            'student_id' => 'sometimes|nullable|string',
            'course_id' => 'sometimes|nullable|string',
            'exam_id' => 'sometimes|nullable|string',
            'specialty_id' => 'sometimes|nullable|string',
            'level_id' => 'sometimes|nullable|string',
            'exam_status' => 'sometimes|nullable|string' ,
            'paid_status' => 'sometimes|nullable|string',
            'resit_fee' => 'sometimes|nullable',
            'attempt_number'=> 'sometimes|nullable',
            'iscarry_over'=> 'sometimes|nullable'
        ];
    }
}
