<?php

namespace App\Http\Requests\ElectionOffice;

use Illuminate\Foundation\Http\FormRequest;

class AppointOfficeHolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'election_role_id' => 'required|string|exists:election_roles,id',
            'holder_type' => 'required|in:student,teacher,school_admin',
            'holder_id' => 'required|string',
            'system_academic_year_id' => 'required|string|exists:system_academic_years,id',
            'school_academic_year_ids' => 'nullable|array|min:1',
            'school_academic_year_ids.*' => 'string|exists:school_academic_years,id',
            'remarks' => 'nullable|string|max:2000',
        ];
    }
}
