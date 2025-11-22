<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateAnnouncementRequest extends FormRequest
{
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
            'status' => 'nullable|string|in:draft,published,scheduled,active',
            'published_at' => 'nullable|date_format:Y-m-d H:i|after_or_equal:today',
            'category_id' => ['required', 'string', 'exists:announcement_categories,id'],
            'label_id' => ['required', 'string', 'exists:labels,id'],
            'tag_ids' => 'required|array',
            'tag_ids.*.tag_id' => 'string|exists:tags,id',

            'teacher_ids' => 'nullable|array',
            'teacher_ids.*.teacher_id' => 'required|string|exists:teachers,id',
            'school_admin_ids' => 'nullable|array',
            'school_admin_ids.*.school_admin_id' => 'required|string|exists:school_admins,id',

            'student_audience' => 'nullable|array',
            'student_audience.*.student_audience_id' => 'required|string|exists:specialties,id',
        ];
    }

    /**
     * Configure the validator instance for custom checks.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $data = $this->all();

            $hasTeacherAudience = !empty($data['teacher_ids']);
            $hasSchoolAdminAudience = !empty($data['school_admin_ids']);
            // Parent Audience check removed
            $hasStudentAudience = !empty($data['student_audience']);
            if (!$hasTeacherAudience && !$hasSchoolAdminAudience && !$hasStudentAudience) {
                $validator->errors()->add(
                    'audience',
                    'You must select at least one recipient audience (Teachers, School Admins, or Students) for the announcement.'
                );
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'audience' => 'Recipient Audience',
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
            // Custom message for the collective audience check, updated
            'audience.required' => 'The **Recipient Audience** field is required. You must select at least one of the available recipient groups (Teachers, School Admins, or Students).'
        ];
    }
}
