<?php

namespace App\Http\Requests\ExamTimetable;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use Illuminate\Foundation\Http\FormRequest;

class GenerateExamTimetableRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            "exam_id" => ["required", "uuid", "exists:exams,id"],
            'version_id' => 'sometimes|nullable|string|exists:timetable_versions,id',

            //operational period
            OperationalPeriod::KEY => ["required", "array"],
            OperationalPeriod::KEY . '.start_time' => ["required", "date_format:H:i"],
            OperationalPeriod::KEY . '.end_time' => [
                "required",
                "date_format:H:i",
                "after:" . OperationalPeriod::KEY . ".start_time"
            ],

            OperationalPeriod::KEY . '.date_exceptions' => ["sometimes", "nullable", "array", "min:1"],
            OperationalPeriod::KEY . '.date_exceptions.*.date' => ['required', "date", "after_or_equal:today"],
            OperationalPeriod::KEY . '.date_exceptions.*.start_time' => ['required', "date_format:H:i"],
            OperationalPeriod::KEY . '.date_exceptions.*.end_time' => [
                'required',
                "date_format:H:i",
                'after_or_equal:' . OperationalPeriod::KEY . '.date_exceptions.*.start_time'
            ],

            //Session Duration
            SessionDuration::KEY => ["required", "array"],
            SessionDuration::KEY . '.duration' => ["required", "numeric", "min:1", "max:1442"],
            SessionDuration::KEY . '.course_exceptions' => ["sometimes", "nullable", "array", "min:1"],
            SessionDuration::KEY . '.course_exceptions.*.course_id' => ['required', "uuid", "exists:courses,id"],
            SessionDuration::KEY . '.course_exceptions.*.duration' => ['required', "numeric", "min:1", "max:1442"],

            //Requested Assignment
            RequestedAssignment::KEY => ["nullable", "array", "min:1"],
            RequestedAssignment::KEY . '.*.course_id' => ["required", "uuid", "exists:courses,id"],
            RequestedAssignment::KEY . '.*.date' => ["required", "date", "after_or_equal:today"],
            RequestedAssignment::KEY . '.*.start_time' => ["required", "date_format:H:i"],
            RequestedAssignment::KEY . '.*.end_time' => [
                "required",
                "date_format:H:i",
                'after_or_equal:' . RequestedAssignment::KEY . '.*.start_time'
            ],
            RequestedAssignment::KEY . '.*.invigilator_id' => ["required", "uuid", "exists:exam_invigilators,id"],
            RequestedAssignment::KEY . '.*.hall_id' => ["required", "uuid", "exists:halls,id"],

            //Course Requested Slot
            CourseTimeRequest::KEY => ["nullable", "array", "min:1"],
            CourseTimeRequest::KEY . '*.course_id' => ['required_with:' . CourseTimeRequest::KEY, "uuid", "exists:courses,id"],
            CourseTimeRequest::KEY . '*.start_time' => ['required_with:' . CourseTimeRequest::KEY, "date_format:H:i"],
            CourseTimeRequest::KEY . '*.end_time' => ['required_with:' . CourseTimeRequest::KEY, "date_format:H:i",  'after_or_equal:' . CourseTimeRequest::KEY . '.*.start_time'],
            CourseTimeRequest::KEY . '*.date' => ['required_with:' . CourseTimeRequest::KEY, "date", "after_or_equal:today"],

            //Invigilator Requested Slot
            InvigilatorRequestedSlot::KEY => ["nullable", "array", "min:1"],
            InvigilatorRequestedSlot::KEY . '.*.invigilator_id' => ['required', "uuid", "exists:invigilators,id"],
            InvigilatorRequestedSlot::KEY . '.*.date' => ["required", 'date', 'after_or_equal:today'],
            InvigilatorRequestedSlot::KEY . '.*.start_time' => ["required", "date_format:H:i"],
            InvigilatorRequestedSlot::KEY . '.*.end_time' => [
                "required",
                "date_format:H:i",
                'after_or_equal:' . InvigilatorRequestedSlot::KEY . '.*.start_time'
            ],

            //Student Daily Load Range
            StudentDailyLoadRange::KEY => ["nullable", "array"],
            StudentDailyLoadRange::KEY . '.max_sessions' => ['required', 'integer', 'min:1', 'max:10'],
            StudentDailyLoadRange::KEY . '.min_sessions' => ['required_with:' . StudentDailyLoadRange::KEY . '.max_sessions', 'integer', 'min:1', 'lte:' . StudentDailyLoadRange::KEY . '.max_sessions'],
            StudentDailyLoadRange::KEY . '.date_exceptions' => ["sometimes", "nullable", "array"],
            StudentDailyLoadRange::KEY . '.date_exceptions.*.date' => ['required', 'date', 'after_or_equal:today'],
            StudentDailyLoadRange::KEY . '.date_exceptions.*.max_sessions' => ['required', 'integer', 'min:1', 'max:10'],
            StudentDailyLoadRange::KEY . '.date_exceptions.*.min_sessions' => ['required', 'integer', 'min:1', 'lte:' . StudentDailyLoadRange::KEY . '.date_exceptions.*.max_sessions']

        ];
    }
}
