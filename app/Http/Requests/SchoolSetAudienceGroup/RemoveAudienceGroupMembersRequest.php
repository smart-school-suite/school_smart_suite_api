<?php

namespace App\Http\Requests\SchoolSetAudienceGroup;

use Illuminate\Foundation\Http\FormRequest;

class RemoveAudienceGroupMembersRequest extends FormRequest
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
            'audience_group_id' => 'required|exists:school_set_audience_groups,id',
            'member_ids' => 'required|array',
            'member_ids.*' => 'required|string|exists:audiences,id',
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
              'audience_group_id.required' => 'The audience group ID is required.',
            'audience_group_id.integer' => 'The audience group ID must be an integer.',
            'audience_group_id.exists' => 'The specified audience group does not exist.',

            'school_admin_ids.array' => 'School admin IDs must be provided as an array.',
            'school_admin_ids.*.integer' => 'Each school admin ID must be an integer.',
            'school_admin_ids.*.exists' => 'One or more provided school admin IDs do not exist.',

            'parent_ids.array' => 'Parent IDs must be provided as an array.',
            'parent_ids.*.integer' => 'Each parent ID must be an integer.',
            'parent_ids.*.exists' => 'One or more provided parent IDs do not exist.',

            'student_ids.array' => 'Student IDs must be provided as an array.',
            'student_ids.*.integer' => 'Each student ID must be an integer.',
            'student_ids.*.exists' => 'One or more provided student IDs do not exist.',

            'teacher_ids.array' => 'Teacher IDs must be provided as an array.',
            'teacher_ids.*.integer' => 'Each teacher ID must be an integer.',
            'teacher_ids.*.exists' => 'One or more provided teacher IDs do not exist.',
        ];
    }
}
