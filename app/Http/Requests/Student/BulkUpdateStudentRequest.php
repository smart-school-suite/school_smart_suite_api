<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateStudentRequest extends FormRequest
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
            'students' => 'required|array',
            'students.*.student_id' => 'required|string|exists:student,id',
            'students.*.email' => 'sometimes|nullable|email',
            'students.*.first_name' => 'sometimes|nullable|string',
            'students.*.last_name' => 'sometimes|nullable|string',
            'students.*.name' => 'sometimes|nullable|string'
        ];
    }
}
