<?php

namespace App\Http\Requests\SemesterTimetableDraft;

use Illuminate\Foundation\Http\FormRequest;

class CreateSemesterTimetableDraftRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_semester_id' => 'required|string|exists:school_semesters,id',
        ];
    }
}
