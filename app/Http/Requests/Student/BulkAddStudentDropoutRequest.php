<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class BulkAddStudentDropoutRequest extends FormRequest
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
            'dropout_list' => 'required|array',
            'dropout_list.*.student_id' => 'required|string|exists:student,id',
           //'dropout_list.*.reason' => 'required|string'
        ];
    }
}
