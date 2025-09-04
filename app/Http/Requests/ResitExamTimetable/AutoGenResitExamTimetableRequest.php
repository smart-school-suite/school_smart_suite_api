<?php

namespace App\Http\Requests\ResitExamTimetable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;
class AutoGenResitExamTimetableRequest extends FormRequest
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
            'exam_id'            => ['required', 'uuid', 'exists:resit_exams,id'], // tweak to: ['required','uuid','exists:exams,id'] if needed
            'start_time'         => ['required', 'date_format:H:i'],
            'end_time'           => ['required', 'date_format:H:i'],
            'min_course_per_day' => ['required', 'integer', 'min:1'],
            'max_course_per_day' => ['required', 'integer', 'min:1', 'max:10', 'gte:min_course_per_day'],
            'course_duration'    => ['required', 'integer', 'min:1', 'max:1440'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.required'          => 'Provide a start time (HH:MM).',
            'start_time.date_format'       => 'Start time must be in 24h HH:MM format.',
            'end_time.required'            => 'Provide an end time (HH:MM).',
            'end_time.date_format'         => 'End time must be in 24h HH:MM format.',
            'min_course_per_day.min'       => 'There must be at least one course per day.',
            'max_course_per_day.gte'       => 'Max courses per day cannot be less than the minimum.',
            'course_duration.min'          => 'Course duration must be at least 1 minute.',
            'course_duration.max'          => 'Course duration cannot exceed 1440 minutes.',
            'exam_id.uuid'                 => 'Invalid exam id.',
            // add 'exam_id.exists' => 'Exam not found.' if you add the exists rule
        ];
    }

    public function withValidator($validator): void
    {
        /** @var Validator $validator */
        $validator->after(function (Validator $v) {
            $data = $this->all();

            // If any required keys are missing, let the base rules surface those errors first.
            foreach (['start_time','end_time','course_duration','min_course_per_day','max_course_per_day'] as $key) {
                if (!array_key_exists($key, $data)) {
                    return;
                }
            }

            // Parse times strictly
            $start = Carbon::createFromFormat('H:i', trim((string)$data['start_time']));
            $end   = Carbon::createFromFormat('H:i', trim((string)$data['end_time']));

            if ($start === false || $end === false) {
                // date_format rule will already flag this
                return;
            }

            // Same-day window only: end must be strictly after start
            if ($end->lessThanOrEqualTo($start)) {
                $v->errors()->add('end_time', 'End time must be after start time.');
                return;
            }

            // Numerics (cast safely)
            $duration   = (int) $data['course_duration'];
            $minCourses = (int) $data['min_course_per_day'];
            $maxCourses = (int) $data['max_course_per_day'];

            // Available minutes in the window
            $availableMinutes = $start->diffInMinutes($end, false); // positive because we checked end > start

            // Basic sanity: duration must fit inside window at least once
            if ($duration > $availableMinutes) {
                $v->errors()->add('course_duration', 'Course duration cannot exceed the total available time window.');
                return; // no need to continue if even one course can’t fit
            }

            // Compute capacity by flooring the division
            $maxPossibleCourses = intdiv($availableMinutes, $duration);

            // Min must be feasible
            if ($minCourses > $maxPossibleCourses) {
                $v->errors()->add(
                    'min_course_per_day',
                    "With a {$duration}-minute duration and a {$availableMinutes}-minute window, you can schedule at most {$maxPossibleCourses} course(s)."
                );
            }

            // Max must be feasible
            if ($maxCourses > $maxPossibleCourses) {
                $v->errors()->add(
                    'max_course_per_day',
                    "With a {$duration}-minute duration and a {$availableMinutes}-minute window, you can schedule at most {$maxPossibleCourses} course(s)."
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'min_course_per_day' => 'minimum courses per day',
            'max_course_per_day' => 'maximum courses per day',
            'course_duration'    => 'course duration (minutes)',
        ];
    }
}
