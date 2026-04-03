<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Schedule;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot;
use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;

class DailyPeriodValidator implements ValidatorInterface
{
    //supported slot types
    protected const TREQUESTEDSLOT = TeacherRequestedTimeSlot::KEY;
    protected const REQUESTEDASSIGNMENT = RequestedAssignment::KEY;
    protected const CREQUESTEDSLOT = CourseRequestedSlot::KEY;
    public function check(ConstraintContext $context, array $params): array
    {

        $teacherId = $params["teacher_id"];
        $startTime = $params["start_time"];
        $endTime = $params["end_time"];
        $slotType = $params["slot_type"];
        $day = $params["day"];
        $dailyPeriodConstraint = $context->dailyPeriodsFor($day);
        $jointCoursesSlots = $context->jointCourses($day);
        $teacherCoursesId = $context->coursesForTeacher($teacherId)->pluck("course_id");
        $teacherRequestedSlot = $context->tRequestedWindowsFor($day)->where("teacher_id", $teacherId);
        $courseRequestedSlot = $context->cRequestedWindowsFor($day)->whereIn("course_id", $teacherCoursesId);
        $requestedAssignment = $context->requestedAssignmentsFor($day)->where("teacher_id", $teacherId);
        return [];
    }
}
