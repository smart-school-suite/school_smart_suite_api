<?php

namespace App\Http\Requests\StudentResit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentResitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'student_id' => 'sometimes|nullable|string',
            'course_id' => 'sometimes|nullable|string',
            'exam_id' => 'sometimes|nullable|string',
            'specialty_id' => 'sometimes|nullable|string|exists:specialties,id',
            'level_id' => 'sometimes|nullable|string|exists:levels,id',
            'exam_status' => 'sometimes|nullable|string' ,
            'paid_status' => 'sometimes|nullable|string',
            'resit_fee' => 'sometimes|nullable',
            'attempt_number'=> 'sometimes|nullable',
            'iscarry_over'=> 'sometimes|nullable'
        ];
    }
}
