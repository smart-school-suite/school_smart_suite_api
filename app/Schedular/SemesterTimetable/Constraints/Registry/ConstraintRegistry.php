<?php

namespace App\Schedular\SemesterTimetable\Constraints\Registry;

use App\Schedular\SemesterTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\SemesterTimetable\Constraints\Handlers\Assignment\RequestedAssignment;
// use App\Schedular\SemesterTimetable\Constraints\Handlers\Course\CourseDailyFrequecy;
// use App\Schedular\SemesterTimetable\Constraints\Handlers\Course\CourseRequestedSlot;
// use App\Schedular\SemesterTimetable\Constraints\Handlers\Course\RequiredJointCourseSlot;
// use App\Schedular\SemesterTimetable\Constraints\Handlers\Hall\HallRequestedTimeWindow;
// use App\Schedular\SemesterTimetable\Constraints\Handlers\Schedule\RequestedFreePeriod;
// use App\Schedular\SemesterTimetable\Constraints\Handlers\Schedule\ScheduleDailyFreePeriod;
// use App\Schedular\SemesterTimetable\Constraints\Handlers\Schedule\ScheduleDailyPeriod;
// use App\Schedular\SemesterTimetable\Constraints\Handlers\Teacher\TeacherDailyHours;
// use App\Schedular\SemesterTimetable\Constraints\Handlers\Teacher\TeacherRequestedWindow;
use App\Schedular\SemesterTimetable\Core\State;

class ConstraintRegistry
{
   protected $handlers = [
        RequestedAssignment::class,
        // CourseDailyFrequecy::class,
        // CourseRequestedSlot::class,
        // RequiredJointCourseSlot::class,
        // HallRequestedTimeWindow::class,
        // RequestedFreePeriod::class,
        // ScheduleDailyFreePeriod::class,
        // ScheduleDailyPeriod::class,
        // TeacherDailyHours::class,
        // TeacherRequestedWindow::class
   ];

   public function enforceConstraints(array $requestPayload, State $state): void
   {
        foreach($this->handlers as $handlerClass) {
            $handler = new $handlerClass();
            if ($handler instanceof ConstraintHandler) {
                $handler->handle($requestPayload, $state);
            }
        }
   }
}
