<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateTeacherRequest extends FormRequest
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
            'teachers' => 'required|array',
            'teachers.*.id' => 'required|string|exists:teacher,id',
            'teachers.*.first_name' => 'sometimes|nullable|string',
            'teachers.*.last_name' => 'sometimes|nullable|string',
            'teachers.*.name' => 'sometimes|nullable|string',
            'teachers.*.email' => 'sometimes|nullable|email',
        ];
    }
}
