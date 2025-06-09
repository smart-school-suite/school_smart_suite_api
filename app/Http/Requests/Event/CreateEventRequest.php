<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventRequest extends FormRequest
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
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:5000',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:2048',
            'organizer' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => ['required', 'string', Rule::in(['active', 'draft', 'scheduled'])],
            'start_date' => 'nullable|date_format:Y-m-d H:i',
            'end_date' => 'nullable|date_format:Y-m-d H:i|after_or_equal:start_date',
            'published_at' => 'nullable|date_format:Y-m-d H:i',
            'event_category_id' => 'required|exists:event_categories,id',
            'tag_id' => 'required|exists:event_tags,id',

            'parent_ids' => ['sometimes', 'array'],
            'parent_ids.*' => ['string', Rule::exists('parents', 'id')],

            'school_admin_ids' => ['sometimes', 'array'],
            'school_admin_ids.*' => ['string', Rule::exists('school_admins', 'id')],

            'student_ids' => ['sometimes', 'array'],
            'student_ids.*' => ['string', Rule::exists('students', 'id')],

            'teacher_ids' => ['sometimes', 'array'],
            'teacher_ids.*' => ['string', Rule::exists('teachers', 'id')],

            'preset_group_ids' => ['sometimes', 'array'],
            'preset_group_ids.*' => ['string', Rule::exists('preset_audiences', 'id')],

            'school_set_group_ids' => ['sometimes', 'array'],
            'school_set_group_ids.*' => ['string', Rule::exists('school_set_audience_groups', 'id')],
        ];


        switch ($this->input('status')) {
            case 'active':
                $rules = array_merge($rules, [
                    'organizer' => 'required|string|max:255',
                    'location' => 'required|string|max:255',
                    'start_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:today',
                    'end_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:start_date',
                    'published_at' => 'nullable',
                ]);
                break;

            case 'scheduled':
                $rules = array_merge($rules, [
                    'organizer' => 'required|string|max:255',
                    'location' => 'required|string|max:255',
                    'start_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:today',
                    'end_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:start_date',
                    'published_at' => 'required|date_format:Y-m-d H:i:s|after:now',
                ]);
                break;

            case 'draft':
                // 'title' and 'description' are already 'required'
                // All other fields can remain 'nullable' or 'sometimes|nullable'
                // The existing 'nullable' rules are sufficient here.
                break;
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
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

            // Only enforce target audience selection if status is 'active' or 'scheduled'
            $status = $this->input('status');
            if ($status === 'active' || $status === 'scheduled') {
                $oneFieldIsPresentAndNotEmpty = false;
                foreach ($allTargetFields as $field) {
                    if ($this->has($field) && !empty($this->input($field))) {
                        $oneFieldIsPresentAndNotEmpty = true;
                        break;
                    }
                }

                if (!$oneFieldIsPresentAndNotEmpty) {
                    $validator->errors()->add('target_audience', 'At least one target audience (parents, school admins, students, teachers, preset groups, or school set groups) must be selected for active or scheduled events.');
                }
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
        $todaysDate = now();
        return [
            'end_date.after_or_equal' => 'The event end date must be after or equal to the start date.',
            'start_date.after_or_equal' => "The event start date must be today's  date: $todaysDate  or a future date.",
            'published_at.after_or_equal' => "The :attribute must be today's date:  $todaysDate or a future date.",
            'published_at.after' => 'The :attribute must be a future date and time for scheduled events.',
            'date_format' => 'The :attribute is not a valid date format. Please use YYYY-MM-DD HH:MM.',
            'tag_id.required' => 'An event tag is required.',
            'event_category_id.required' => 'An event category is required.',
            '*.exists' => 'One or more selected IDs are invalid.',
        ];
    }
}
