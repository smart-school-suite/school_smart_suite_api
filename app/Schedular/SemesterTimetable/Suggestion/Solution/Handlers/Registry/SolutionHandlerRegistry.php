<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Registry;

use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Assignment\RequiredAssignmentSolutionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Course\CourseRequestedTimeSlotSolutionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Hall\HallBusySolutionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Hall\HallRequestedTimeSlotSolutionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Schedule\BreakPeriodSolutionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Schedule\OperationalPeriodSolutionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Schedule\PeriodDurationSolutionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Schedule\RequestedFreePeriodSolutionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Teacher\TeacherBusySolutionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Teacher\TeacherRequestedTimeSlotSolutionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Teacher\TeacherUnavailableSolutionHandler;

class SolutionHandlerRegistry
{
    protected $map = [
        RequiredAssignmentSolutionHandler::class,
        CourseRequestedTimeSlotSolutionHandler::class,
        HallBusySolutionHandler::class,
        HallRequestedTimeSlotSolutionHandler::class,
        BreakPeriodSolutionHandler::class,
        OperationalPeriodSolutionHandler::class,
        PeriodDurationSolutionHandler::class,
        RequestedFreePeriodSolutionHandler::class,
        TeacherBusySolutionHandler::class,
        TeacherRequestedTimeSlotSolutionHandler::class,
        TeacherUnavailableSolutionHandler::class,
    ];

    public function getHandler(string $targetType) {
         foreach($this->map as $handlerClass){
            $handler = new $handlerClass();
            if($handler->supports($targetType)){
                return $handler;
            }
         }
    }
}
