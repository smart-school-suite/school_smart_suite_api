<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAnnouncementRequest extends FormRequest
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
            'title' => 'required|string|max:150',
            'content' => 'required|string|max:5000',
            'status' => 'nullable|string|in:draft,published',
            'published_at' => 'nullable|date_format:Y-m-d H:i|after_or_equal:today',
            'expires_at' => 'nullable|date_format:Y-m-d H:i|after_or_equal:published_at',
            'category_id' => ['required', 'string', 'exists:announcement_categories,id'],
            'label_id' => ['required', 'string', 'exists:labels,id'],
            'tag_id' => ['required', 'string', 'exists:tags,id'],
            'parent_ids' => [
                'sometimes',
                'array',
            ],
            'parent_ids.*' => [
                'string',
                Rule::exists('parents', 'id'),
            ],

            'school_admin_ids' => [
                'sometimes',
                'array',
            ],
            'school_admin_ids.*' => [
                'string',
                Rule::exists('school_admins', 'id'),
            ],

            'student_ids' => [
                'sometimes',
                'array',
            ],
            'student_ids.*' => [
                'string',
                Rule::exists('student', 'id'),
            ],

            'teacher_ids' => [
                'sometimes',
                'array',
            ],
            'teacher_ids.*' => [
                'string',
                Rule::exists('teachers', 'id'),
            ],

            'preset_group_ids' => [
                'sometimes',
                'array',
            ],
            'preset_group_ids.*' => [
                'string',
                Rule::exists('preset_audiences', 'id'),
            ],

            'school_set_group_ids' => [
                'sometimes',
                'array',
            ],
            'school_set_group_ids.*' => [
                'string',
                Rule::exists('school_set_audience_groups', 'id'),
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $allTargetFields = [
                'parent_ids',
                'school_admin_ids',
                'student_ids',
                'teacher_ids',
                'preset_group_ids',
                'school_set_group_ids',
            ];

            $oneFieldIsPresentAndNotEmpty = false;
            foreach ($allTargetFields as $field) {
                // Check if the field exists in the request and is not an empty array or null.
                // For 'array' fields, `empty()` correctly checks if the array is empty.
                if ($this->has($field) && !empty($this->input($field))) {
                    $oneFieldIsPresentAndNotEmpty = true;
                    break;
                }
            }

            // If none of the target audience fields are present or they are all empty, add an error.
            if (!$oneFieldIsPresentAndNotEmpty) {
                $validator->errors()->add('target_audience', 'At least one target audience (parents, school admins, students, teachers, preset groups, or school set groups) must be selected.');
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // The 'required_without_all' message is now replaced by the custom message from withValidator.
            'target_audience.required_without_all' => 'At least one target audience (parents, school admins, students, teachers, preset groups, or school set groups) must be selected.',
            'published_at.after_or_equal' => 'The :attribute must be today\'s date or a future date.',
            'expires_at.after_or_equal' => 'The :attribute must be after or equal to the published date.',
            'date_format' => 'The :attribute is not a valid date format. Please use YYYY-MM-DD HH:MM:SS.',
        ];
    }
}
