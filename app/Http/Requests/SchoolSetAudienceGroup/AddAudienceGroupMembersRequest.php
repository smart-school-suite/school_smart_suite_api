<?php

namespace App\Http\Requests\SchoolSetAudienceGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class AddAudienceGroupMembersRequest extends FormRequest
{
/**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Implement your authorization logic here
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'audience_group_id' => [
                'required',
                'string',
                Rule::exists('school_set_audience_groups', 'id'),
            ],
            'school_admin_ids' => ['sometimes', 'array'],
            'school_admin_ids.*' => [
                'string',
                Rule::exists('school_admin', 'id'),
            ],

            'parent_ids' => ['sometimes', 'array'],
            'parent_ids.*' => [
                'string',
                Rule::exists('parents', 'id'),
            ],

            'student_ids' => ['sometimes', 'array'],
            'student_ids.*' => [
                'string',
                Rule::exists('student', 'id'),
            ],

            'teacher_ids' => ['sometimes', 'array'],
            'teacher_ids.*' => [
                'string',
                Rule::exists('teachers', 'id'),
            ],

            'all_members' => [
                'required',
                function ($attribute, $value, $fail) {
                    $hasMembers = !empty($this->input('school_admin_ids')) ||
                                  !empty($this->input('parent_ids')) ||
                                  !empty($this->input('student_ids')) ||
                                  !empty($this->input('teacher_ids'));

                    if (!$hasMembers) {
                        $fail('At least one of "school_admin_ids", "parent_ids", "student_ids", or "teacher_ids" must contain members.');
                    }
                },
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'audience_group_id.required' => 'An audience group ID is required.',
            'audience_group_id.integer' => 'The audience group ID must be an integer.',
            'audience_group_id.exists' => 'The selected audience group does not exist.',

            'school_admin_ids.array' => 'School admin IDs must be an array.',
            'school_admin_ids.*.integer' => 'Each school admin ID must be an integer.',
            'school_admin_ids.*.exists' => 'One or more provided school admin IDs do not exist.',

            'parent_ids.array' => 'Parent IDs must be an array.',
            'parent_ids.*.integer' => 'Each parent ID must be an integer.',
            'parent_ids.*.exists' => 'One or more provided parent IDs do not exist.',

            'student_ids.array' => 'Student IDs must be an array.',
            'student_ids.*.integer' => 'Each student ID must be an integer.',
            'student_ids.*.exists' => 'One or more provided student IDs do not exist.',

            'teacher_ids.array' => 'Teacher IDs must be an array.',
            'teacher_ids.*.integer' => 'Each teacher ID must be an integer.',
            'teacher_ids.*.exists' => 'One or more provided teacher IDs do not exist.',

            // The custom error message is defined within the closure for 'all_members'
        ];
    }

    /**
     * Prepare the data for validation.
     * This method adds a dummy 'all_members' field to trigger the custom validation.
     * Laravel validates all input fields, so we need a field to attach the custom rule to.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'all_members' => 'dummy_value',
        ]);
    }
}
