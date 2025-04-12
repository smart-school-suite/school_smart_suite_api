<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateStudentRequest extends FormRequest
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
           'students' => 'required|array',
           'students.*.student_id' => 'required|string|exists:student,id',
           'students.*.email' => 'sometimes|nullable|email',
           'students.*.first_name' => 'sometimes|nullable|string',
           'students.*.last_name' => 'sometimes|nullable|string',
           'students.*.name' => 'sometimes|nullable|string'
        ];
    }
}
