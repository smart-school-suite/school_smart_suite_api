<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Core;

use App\Schedular\SemesterTimetable\Suggestion\Resolution\Assignment\RequestedAssignmentRes;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Course\CourseRequestedTimeSlotRes;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Hall\HallBusyRes;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Hall\HallRequestedTimeSlotRes;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule\BreakPeriodRes;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule\OperationalPeriodRes;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule\RequestedFreePeriodRes;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule\SchedulePeriodDurationRes;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Teacher\TeacherBusyRes;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Teacher\TeacherRequestedTimeSlotRes;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Teacher\TeacherUnavailableRes;

class ResolutionRegistry
{
    protected array $map = [
        RequestedAssignmentRes::class,
        CourseRequestedTimeSlotRes::class,
        HallBusyRes::class,
        HallRequestedTimeSlotRes::class,
        BreakPeriodRes::class,
        OperationalPeriodRes::class,
        SchedulePeriodDurationRes::class,
        RequestedFreePeriodRes::class,
        TeacherBusyRes::class,
        TeacherRequestedTimeSlotRes::class,
        TeacherUnavailableRes::class
    ];

    public function handle($resolution){
         foreach($this->map as $resolver){
             $resolver = new $resolver();
             if($resolver->supports($resolution->target_type)){
                 return $resolver;
             }
         }
    }

}
