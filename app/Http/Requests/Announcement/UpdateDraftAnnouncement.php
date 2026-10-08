<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDraftAnnouncement extends FormRequest
{

    public function rules(): array
    {
        return [
            'announcement_id' => 'required|string|exists:announcements,id',
            'title' => 'required|string|max:150',
            'content' => 'required|string|max:5000',
            'status' => 'nullable|string|in:draft,published,scheduled,active',
            'published_at' => 'nullable|date_format:Y-m-d H:i|after_or_equal:today',
            'category_id' => ['nullable', 'string', 'exists:announcement_categories,id'],
            'label_id' => ['nullable', 'string', 'exists:labels,id'],
            'tag_ids' => 'nullable|array',
            'tag_ids.*.tag_id' => 'nullable|string|exists:tags,id',

            'school_wide' => 'nullable|boolean',

            // Admin Audience
            'admin_audience' => 'nullable|array',
            'admin_audience.*.individual_ids' => 'nullable|array',
            'admin_audience.*.individual_ids.*' => 'uuid|exists:school_admins,id',

            // Student Audience
            'student_audience' => 'nullable|array',
            'student_audience.*.department_ids' => 'nullable|array',
            'student_audience.*.department_ids.*' => 'uuid|exists:departments,id',
            'student_audience.*.specialty_ids' => 'nullable|array',
            'student_audience.*.specialty_ids.*' => 'uuid|exists:specialties,id',
            'student_audience.*.level_ids' => 'nullable|array',
            'student_audience.*.level_ids.*' => 'uuid|exists:levels,id',
            'student_audience.*.individual_ids' => 'nullable|array',
            'student_audience.*.individual_ids.*' => 'uuid|exists:students,id',

            // Teacher Audience
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
}
