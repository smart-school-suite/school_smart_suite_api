<?php

namespace App\Http\Requests\ExamTimetable;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Settings for one exam timetable generation. Keys match the scheduler's constraint keys, so a solution step
 * edits this request directly. Invigilator ids are exam invigilator ids (exam_invigilators.id).
 */
class GenerateExamTimetableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'string', 'exists:exams,id'],
            'version_id' => ['sometimes', 'nullable', 'string', 'exists:exam_timetable_versions,id'],

            // Opening hours on exam dates
            OperationalPeriod::KEY => ['required', 'array'],
            OperationalPeriod::KEY . '.start_time' => ['required', 'date_format:H:i'],
            OperationalPeriod::KEY . '.end_time' => ['required', 'date_format:H:i', 'after:' . OperationalPeriod::KEY . '.start_time'],
            OperationalPeriod::KEY . '.slot_step_minutes' => ['sometimes', 'nullable', 'integer', 'min:5', 'max:120'],
            OperationalPeriod::KEY . '.date_exceptions' => ['sometimes', 'nullable', 'array'],
            OperationalPeriod::KEY . '.date_exceptions.*.date' => ['required', 'date'],
            OperationalPeriod::KEY . '.date_exceptions.*.start_time' => ['required', 'date_format:H:i'],
            OperationalPeriod::KEY . '.date_exceptions.*.end_time' => ['required', 'date_format:H:i', 'after:' . OperationalPeriod::KEY . '.date_exceptions.*.start_time'],
            OperationalPeriod::KEY . '.excluded_dates' => ['sometimes', 'nullable', 'array'],
            OperationalPeriod::KEY . '.excluded_dates.*' => ['date'],
            OperationalPeriod::KEY . '.excluded_days' => ['sometimes', 'nullable', 'array'],
            OperationalPeriod::KEY . '.excluded_days.*' => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],

            // Exam length
            SessionDuration::KEY => ['required', 'array'],
            SessionDuration::KEY . '.duration' => ['required', 'integer', 'min:1', 'max:1440'],
            SessionDuration::KEY . '.gap_minutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:240'],
            SessionDuration::KEY . '.course_exceptions' => ['sometimes', 'nullable', 'array'],
            SessionDuration::KEY . '.course_exceptions.*.course_id' => ['required', 'string', 'exists:courses,id'],
            SessionDuration::KEY . '.course_exceptions.*.duration' => ['required', 'integer', 'min:1', 'max:1440'],

            // A course at a fixed date and time, optionally with a hall and an invigilator
            RequestedAssignment::KEY => ['sometimes', 'nullable', 'array'],
            RequestedAssignment::KEY . '.*.course_id' => ['required', 'string', 'exists:courses,id'],
            RequestedAssignment::KEY . '.*.date' => ['required', 'date'],
            RequestedAssignment::KEY . '.*.start_time' => ['required', 'date_format:H:i'],
            RequestedAssignment::KEY . '.*.end_time' => ['required', 'date_format:H:i', 'after:' . RequestedAssignment::KEY . '.*.start_time'],
            RequestedAssignment::KEY . '.*.invigilator_id' => ['sometimes', 'nullable', 'string', 'exists:exam_invigilators,id'],
            RequestedAssignment::KEY . '.*.hall_id' => ['sometimes', 'nullable', 'string', 'exists:halls,id'],

            // A course at a requested date and time
            CourseTimeRequest::KEY => ['sometimes', 'nullable', 'array'],
            CourseTimeRequest::KEY . '.*.course_id' => ['required', 'string', 'exists:courses,id'],
            CourseTimeRequest::KEY . '.*.date' => ['required', 'date'],
            CourseTimeRequest::KEY . '.*.start_time' => ['required', 'date_format:H:i'],
            CourseTimeRequest::KEY . '.*.end_time' => ['required', 'date_format:H:i', 'after:' . CourseTimeRequest::KEY . '.*.start_time'],

            // An invigilator who should invigilate during a time
            InvigilatorRequestedSlot::KEY => ['sometimes', 'nullable', 'array'],
            InvigilatorRequestedSlot::KEY . '.*.invigilator_id' => ['required', 'string', 'exists:exam_invigilators,id'],
            InvigilatorRequestedSlot::KEY . '.*.date' => ['required', 'date'],
            InvigilatorRequestedSlot::KEY . '.*.start_time' => ['required', 'date_format:H:i'],
            InvigilatorRequestedSlot::KEY . '.*.end_time' => ['required', 'date_format:H:i', 'after:' . InvigilatorRequestedSlot::KEY . '.*.start_time'],

            // A hall that should be used during a time
            HallRequestedSlot::KEY => ['sometimes', 'nullable', 'array'],
            HallRequestedSlot::KEY . '.*.hall_id' => ['required', 'string', 'exists:halls,id'],
            HallRequestedSlot::KEY . '.*.date' => ['required', 'date'],
            HallRequestedSlot::KEY . '.*.start_time' => ['required', 'date_format:H:i'],
            HallRequestedSlot::KEY . '.*.end_time' => ['required', 'date_format:H:i', 'after:' . HallRequestedSlot::KEY . '.*.start_time'],

            // Exams per day for the students
            StudentDailyLoadRange::KEY => ['sometimes', 'nullable', 'array'],
            StudentDailyLoadRange::KEY . '.max_sessions' => ['required_with:' . StudentDailyLoadRange::KEY, 'integer', 'min:1', 'max:10'],
            StudentDailyLoadRange::KEY . '.min_sessions' => ['sometimes', 'nullable', 'integer', 'min:0', 'lte:' . StudentDailyLoadRange::KEY . '.max_sessions'],
            StudentDailyLoadRange::KEY . '.date_exceptions' => ['sometimes', 'nullable', 'array'],
            StudentDailyLoadRange::KEY . '.date_exceptions.*.date' => ['required', 'date'],
            StudentDailyLoadRange::KEY . '.date_exceptions.*.max_sessions' => ['required', 'integer', 'min:1', 'max:10'],
            StudentDailyLoadRange::KEY . '.date_exceptions.*.min_sessions' => ['sometimes', 'nullable', 'integer', 'min:0', 'lte:' . StudentDailyLoadRange::KEY . '.date_exceptions.*.max_sessions'],
        ];
    }
}
