<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class BulkTeacherPreferenceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'teacherIds' => 'required|array',
            'teacherIds.*.teacher_id' => 'required|string|exists:teachers,id',
            'specialtyIds' => 'required|array',
            'specialtyIds.*.specialty_id' => 'required|string|exists:specialties,id'
        ];
    }
}
