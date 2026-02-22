<?php

namespace App\Interpreter\SemesterTimetable\Core;

use App\Constant\Constraint\SemesterTimetable\Soft\MaxPeriodPerDayConstraint;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Assignment\RequestedAssignmentInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Course\CourseMaxDailyFrequencyInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Course\CourseRequestedTimeSlotInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Course\RequiredJointCoursePeriodInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Hall\HallRequestedTimeWindowsInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Schedule\BreakPeriodInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Schedule\MaxFreePeriodPerDayInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Schedule\PeriodDurationInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Schedule\RequestedFreePeriodInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Teacher\TeacherMaxDailyHourInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Teacher\TeacherMaxWeeklyHourInterpreter;
use App\Interpreter\SemesterTimetable\Interpreters\Teacher\TeacherRequestedTimeWindowInterpreter;

class InterpreterRegistry
{
    protected array $map = [
        'break_period' => BreakPeriodInterpreter::class,
        'schedule_period_duration_minutes' => PeriodDurationInterpreter::class,
        'required_joint_course_periods' => RequiredJointCoursePeriodInterpreter::class,
        'teacher_max_daily_hours' => TeacherMaxDailyHourInterpreter::class,
        'teacher_max_weekly_hours' => TeacherMaxWeeklyHourInterpreter::class,
        'schedule_max_periods_per_day' => MaxPeriodPerDayConstraint::class,
        'schedule_max_free_periods_per_day' => MaxFreePeriodPerDayInterpreter::class,
        'course_max_daily_frequency' => CourseMaxDailyFrequencyInterpreter::class,
        'course_requested_time_slots' => CourseRequestedTimeSlotInterpreter::class,
        'requested_assignments' => RequestedAssignmentInterpreter::class,
        'hall_requested_time_windows' => HallRequestedTimeWindowsInterpreter::class,
        'teacher_requested_time_windows' => TeacherRequestedTimeWindowInterpreter::class,
        'requested_free_periods' => RequestedFreePeriodInterpreter::class,
    ];

    public function resolve(string $constraint): ?ConstraintInterpreter
    {
        return isset($this->map[$constraint])
            ? app($this->map[$constraint])
            : null;
    }
}
