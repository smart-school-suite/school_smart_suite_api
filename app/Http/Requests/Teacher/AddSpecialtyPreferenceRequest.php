<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Import DB facade for direct database queries

class AddSpecialtyPreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method ensures that the user is authenticated and that
     * a current school branch has been identified from the request attributes.
     * Without a current school, we cannot determine the tenant for uniqueness checks.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolBranchId = $this->attributes->get('currentSchool')->id;

        return [
            'specailties_preference' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $specialtyIds = collect($value)->pluck('specialty_id')->filter()->all();
                    if (count($specialtyIds) !== count(array_unique($specialtyIds))) {
                        $fail('Each specialty preference in the request must be for a unique specialty. Duplicate specialty IDs found.');
                    }
                },
            ],
            'specailties_preference.*.specialty_id' => [
                'required',
                'string',
                'exists:specialties,id',
                function ($attribute, $value, $fail) use ($schoolBranchId) {
                    $index = explode('.', $attribute)[1];
                    $teacherId = $this->input("specailties_preference.$index.teacher_id");
                    if (empty($teacherId)) {
                        return;
                    }
                    $exists = DB::table('teacher_specialty_preferences')
                                ->where('specialty_id', $value)
                                ->where('teacher_id', $teacherId)
                                ->where('school_branch_id', $schoolBranchId)
                                ->exists();
                    if ($exists) {
                        $fail("Specialty ID {$value} is already preferred by teacher {$teacherId} in this school branch.");
                    }
                },
            ],
            'specailties_preference.*.teacher_id' => [
                'required',
                'string',
                'exists:teachers,id',
            ],
        ];
    }
}
