<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Course;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot;
use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Carbon\Carbon;

class CourseDailyFrequecyValidator implements ValidatorInterface
{
    protected const TREQUESTEDSLOT = TeacherRequestedTimeSlot::KEY;
    protected const REQUESTEDASSIGNMENT = RequestedAssignment::KEY;
    protected const CREQUESTEDSLOT = CourseRequestedSlot::KEY;
    public function check(ConstraintContext $context, array $params): array
    {
        $courseId = $params["course_id"];
        $startTime = $params["start_time"];
        $endTime = $params["end_time"];
        $slotType = $params["slot_type"];
        $day = $params["day"];
        // $dailyPeriodConstraint = $context->dailyPeriodsFor($day);
        // $jointCoursesSlots = $context->jointCourses($day);
        // $teacherCoursesId = $context->teachersForCourse($courseId)->pluck("course_id");
        // $teacherRequestedSlot = $context->tRequestedWindowsFor($day)->where("teacher_id", $teacherId);
        // $courseRequestedSlot = $context->cRequestedWindowsFor($day)->whereIn("course_id", $teacherCoursesId);
        // $requestedAssignment = $context->requestedAssignmentsFor($day)->where("teacher_id", $teacherId);
        return [];
    }
}
