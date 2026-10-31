<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateEventRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('school_wide')) {
            $this->merge([
                'school_wide' => filter_var($this->input('school_wide'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:5000',
            'organizer' => 'required|string|max:200',
            'location' => 'required|string|max:150',
            'status' => ['required', 'string', Rule::in(['draft', 'active', 'scheduled'])],
            'start_date' => 'required|date_format:Y-m-d H:i',
            'end_date' => 'required|date_format:Y-m-d H:i|after_or_equal:start_date',
            'published_at' => 'nullable|date_format:Y-m-d H:i|before_or_equal:start_date',
            'event_category_id' => 'required|uuid|exists:event_categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*.tag_id' => 'required|uuid|exists:event_tags,id',

            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',

            'school_wide' => 'nullable|boolean',

            'admin_audience' => 'nullable|array',
            'admin_audience.*.individual_ids' => 'nullable|array',
            'admin_audience.*.individual_ids.*' => 'uuid|exists:school_admins,id',

            'student_audience' => 'nullable|array',
            'student_audience.*.department_ids' => 'nullable|array',
            'student_audience.*.department_ids.*' => 'uuid|exists:departments,id',
            'student_audience.*.specialty_ids' => 'nullable|array',
            'student_audience.*.specialty_ids.*' => 'uuid|exists:specialties,id',
            'student_audience.*.level_ids' => 'nullable|array',
            'student_audience.*.level_ids.*' => 'uuid|exists:levels,id',
            'student_audience.*.individual_ids' => 'nullable|array',
            'student_audience.*.individual_ids.*' => 'uuid|exists:students,id',

            'teacher_audience' => 'nullable|array',
            'teacher_audience.*.department_ids' => 'nullable|array',
            'teacher_audience.*.department_ids.*' => 'uuid|exists:departments,id',
            'teacher_audience.*.specialty_ids' => 'nullable|array',
            'teacher_audience.*.specialty_ids.*' => 'uuid|exists:specialties,id',
            'teacher_audience.*.level_ids' => 'nullable|array',
            'teacher_audience.*.level_ids.*' => 'uuid|exists:levels,id',
            'teacher_audience.*.individual_ids' => 'nullable|array',
            'teacher_audience.*.individual_ids.*' => 'uuid|exists:teachers,id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('status') === 'draft' || $this->boolean('school_wide')) {
                return;
            }

            $data = $this->all();

            $hasAdminAudience = !empty(array_filter($data['admin_audience'] ?? []));
            $hasStudentAudience = !empty(array_filter($data['student_audience'] ?? []));
            $hasTeacherAudience = !empty(array_filter($data['teacher_audience'] ?? []));

            if (!$hasAdminAudience && !$hasStudentAudience && !$hasTeacherAudience) {
                $validator->errors()->add(
                    'audience',
                    'You must select at least one invitee audience (Teachers, School Admins, or Students) or make the event school wide.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'audience' => 'Invitee Audience',
            'images' => 'Event Images',
            'images.*' => 'Event Image',
        ];
    }

    public function messages(): array
    {
        return [
            'images.max' => 'An event can have at most 5 images.',
            'images.*.max' => 'Each event image must be 2 MB or smaller.',
        ];
    }
}
