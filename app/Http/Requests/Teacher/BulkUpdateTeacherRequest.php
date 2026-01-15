<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateTeacherRequest extends FormRequest
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
            'teachers' => 'required|array',
            'teachers.*.id' => 'required|string|exists:teachers,id',
            'teachers.*.first_name' => 'sometimes|nullable|string',
            'teachers.*.last_name' => 'sometimes|nullable|string',
            'teachers.*.name' => 'sometimes|nullable|string',
            'teachers.*.email' => 'sometimes|nullable|email',
            'teachers.*.gender_id' => 'sometimes|nullable|string|exists:genders,id'
        ];
    }
}
