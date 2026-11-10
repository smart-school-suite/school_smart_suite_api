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

    // Fields of which a draft must have at least one; a published event needs them all.
    public const DRAFT_DETAIL_FIELDS = ['title', 'description', 'organizer', 'location', 'start_date', 'end_date', 'event_category_id'];

    public function rules(): array
    {
        // Drafts may leave any detail empty; publishing (active / scheduled) needs every one.
        $required = $this->input('status') === 'draft' ? 'nullable' : 'required';
        $hasStart = filled($this->input('start_date'));

        return [
            'title' => "{$required}|string|max:150",
            'description' => "{$required}|string|max:5000",
            'organizer' => "{$required}|string|max:200",
            'location' => "{$required}|string|max:150",
            'status' => ['required', 'string', Rule::in(['draft', 'active', 'scheduled'])],
            'start_date' => "{$required}|date_format:Y-m-d H:i",
            'end_date' => "{$required}|date_format:Y-m-d H:i" . ($hasStart ? '|after_or_equal:start_date' : ''),
            'published_at' => 'nullable|date_format:Y-m-d H:i' . ($hasStart ? '|before_or_equal:start_date' : ''),
            'event_category_id' => "{$required}|uuid|exists:event_categories,id",
            'tag_ids' => 'nullable|array',
            'tag_ids.*.tag_id' => 'required|uuid|exists:event_tags,id',

            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
            // display order: saved image ids and "new:N" for images[N]; the first is the cover
            'image_order' => 'nullable|array|max:5',
            'image_order.*' => ['string', 'regex:/^(new:[0-4]|[0-9a-fA-F-]{36})$/'],

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
            if ($this->input('status') === 'draft') {
                if (!$this->hasDraftContent()) {
                    $validator->errors()->add(
                        'draft',
                        'Add at least one detail (for example a title, description, date, image or invitee) before saving a draft.'
                    );
                }
                return;
            }

            if ($this->boolean('school_wide')) {
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

    private function hasDraftContent(): bool
    {
        foreach (self::DRAFT_DETAIL_FIELDS as $field) {
            if (filled($this->input($field))) {
                return true;
            }
        }

        $hasAudience = collect(['admin_audience', 'student_audience', 'teacher_audience'])
            ->contains(fn($key) => collect($this->input($key, []))->flatten()->filter()->isNotEmpty());

        return $this->boolean('school_wide')
            || $hasAudience
            || collect($this->input('tag_ids', []))->pluck('tag_id')->filter()->isNotEmpty()
            || !empty(array_filter((array) $this->file('images', [])));
    }

    public function attributes(): array
    {
        return [
            'audience' => 'Invitee Audience',
            'draft' => 'Draft',
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
