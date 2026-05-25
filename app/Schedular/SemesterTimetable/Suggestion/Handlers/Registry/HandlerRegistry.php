<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Registry;

use App\Schedular\SemesterTimetable\Suggestion\Handlers\Assignment\AssignmentHandler;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Course\CourseRequestedTimeSlotHandler;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Course\RequiredJointCourseHandler;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Hall\HallRequestedTimeSlotHandler;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule\BreakPeriodHandler;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule\OperationalPeriodHandler;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule\PeriodDurationHandler;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule\RequestedFreePeriodHandler;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Teacher\TeacherRequestedTimeSlotHandler;

class HandlerRegistry
{
    protected array $handlers;

    public function __construct()
    {
        $this->handlers = [
            new AssignmentHandler(),
            new CourseRequestedTimeSlotHandler(),
            new RequiredJointCourseHandler(),
            new HallRequestedTimeSlotHandler(),
            new BreakPeriodHandler(),
            new OperationalPeriodHandler(),
            new PeriodDurationHandler(),
            new RequestedFreePeriodHandler(),
            new TeacherRequestedTimeSlotHandler()
        ];
    }

    public function get(string $type): ?SuggestionHandler
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($type)) {
                return $handler;
            }
        }

        return null;
    }
}
