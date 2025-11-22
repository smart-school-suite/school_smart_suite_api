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
            'status' => 'nullable|string|in:published,scheduled,active',
            'published_at' => 'nullable|date_format:Y-m-d H:i|after_or_equal:today',
            'category_id' => ['required', 'string', 'exists:announcement_categories,id'],
            'label_id' => ['required', 'string', 'exists:labels,id'],
            'tag_ids' => 'required|array',
            'tag_ids.*.tag_id' => 'string|exists:tags,id',

            'teacher_ids' => 'nullable|array',
            'teacher_ids.*.teacher_id' => 'required|string|exists:teacher,id',
            'school_admin_ids' => 'nullable|array',
            'school_admin_ids.*.school_admin_id' => 'required|string|exists:school_admins,id',

            'student_audience' => 'nullable|array',
            'student_audience.*.student_audience_id' => 'required|string|exists:specialties,id',
        ];
    }
}
