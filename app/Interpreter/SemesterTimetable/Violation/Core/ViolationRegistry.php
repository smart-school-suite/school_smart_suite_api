<?php

namespace App\Interpreter\SemesterTimetable\Violation\Core;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Assignment\RequestedAssignmentViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\CourseMaxDailyFrequencyViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\CourseRequestedTimeSlotViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\RequiredJointCourseViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Hall\HallBusy;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Hall\HallRequestedTimeSlotViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\BreakPeriodViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\MaxDailyPeriodViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\MaxFreePeriodPerDayViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\OperationalPeriodViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\PeriodDurationViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\RequestedFreePeriodViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherBusy;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherInsufficiency;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherMaxDailyHourViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherMaxWeeklyHourViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherRequestedTimeSlotViolation;
use App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherUnavailable;

class ViolationRegistry
{
    protected array $map = [
        'break_period_violation' => BreakPeriodViolation::class,
        'operational_period_violation' => OperationalPeriodViolation::class,
        'joint_course_period_violation' => RequiredJointCourseViolation::class,
        'period_duration_violation' => PeriodDurationViolation::class,
        'course_requested_time_slot_violation' => CourseRequestedTimeSlotViolation::class,
        'hall_requested_time_slot_violation' => HallRequestedTimeSlotViolation::class,
        'max_course_daily_frequency_violation' => CourseMaxDailyFrequencyViolation::class,
        'max_daily_free_period_violation' =>MaxFreePeriodPerDayViolation::class,
        'max_daily_period_violation' =>  MaxDailyPeriodViolation::class,
        'max_teacher_daily_hour_violation' => TeacherMaxDailyHourViolation::class,
        'max_teacher_weekly_hour_violation' => TeacherMaxWeeklyHourViolation::class,
        'requested_assignment_violation' => RequestedAssignmentViolation::class,
        'requested_free_period_violation' => RequestedFreePeriodViolation::class,
        'teacher_requested_time_slot_violation' => TeacherRequestedTimeSlotViolation::class,
        'teacher_unavailable' => TeacherUnavailable::class,
        'teacher_busy' => TeacherBusy::class,
        'hall_busy' => HallBusy::class,
        'teacher_insufficiency' => TeacherInsufficiency::class
    ];

    public function resolve(string $violation): ?ViolationInterpreter {
        return isset($this->map[$violation])
            ? app($this->map[$violation])
            : null;
    }
}
