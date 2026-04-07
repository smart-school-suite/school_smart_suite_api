<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core;

use App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment;
use App\Constant\Violation\SemesterTimetable\Course\CourseDailyFrequency;
use App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot;
use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse;
use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Constant\Violation\SemesterTimetable\Hall\HallRequestedTimeSlot;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyFreePeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyPeriod;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherDailyHours;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Assignment\RequestedAssignmentBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course\CourseDailyFrequencyBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course\CourseRequestedTimeSlotBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course\RequiredJointCourseBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Hall\HallBusyBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Hall\HallRequestedTimeSlotBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\BreakPeriodBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\OperationalPeriodBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\PeriodDurationBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\RequestedFreePeriodBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\ScheduleDailyFreePeriodBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher\TeacherBusyBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher\TeacherDailyHourBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher\TeacherRequestedTimeSlotBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher\TeacherUnavailableBlocker;
use Illuminate\Support\Collection;

class BlockerRegistry
{
    protected array $builderMap = [
        //assignment
        RequestedAssigment::KEY => RequestedAssignmentBlocker::class,
        //course
        CourseDailyFrequency::KEY => CourseDailyFrequencyBlocker::class,
        CourseRequestedSlot::KEY => CourseRequestedTimeSlotBlocker::class,
        RequiredJointCourse::KEY => RequiredJointCourseBlocker::class,
        //hall
        HallBusy::KEY => HallBusyBlocker::class,
        HallRequestedTimeSlot::KEY => HallRequestedTimeSlotBlocker::class,
        //schedule
        BreakPeriod::KEY => BreakPeriodBlocker::class,
        OperationalPeriod::KEY => OperationalPeriodBlocker::class,
        PeriodDuration::KEY => PeriodDurationBlocker::class,
        RequestedFreePeriod::KEY => RequestedFreePeriodBlocker::class,
        ScheduleDailyFreePeriod::KEY => ScheduleDailyFreePeriodBlocker::class,
        ScheduleDailyPeriod::KEY => ScheduleDailyFreePeriodBlocker::class,
        //teacher
        TeacherBusy::KEY => TeacherBusyBlocker::class,
        TeacherDailyHours::KEY => TeacherDailyHourBlocker::class,
        TeacherRequestedTimeSlot::KEY => TeacherRequestedTimeSlotBlocker::class,
        TeacherUnavailable::KEY => TeacherUnavailableBlocker::class
    ];

    public function build($blockers): Collection {
        $violations = collect();
        foreach ($blockers as $blocker) {
            $type = $blocker["key"];
            if (isset($this->builderMap[$type])) {
                $builderClass = $this->builderMap[$type];
                $builder = new $builderClass();
                $violation = $builder->build($blocker);
                $violations->push($violation);
            }
        }

        return $violations;
    }
}
