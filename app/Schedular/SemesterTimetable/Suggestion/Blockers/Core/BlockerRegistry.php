<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Blockers\Core;

use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Course\CourseRequestedTimeSlotSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Hall\HallBusySuggestion;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Hall\HallRequestedTimeSlotSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Schedule\BreakPeriodSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Schedule\OperationalPeriodSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Schedule\PeriodDurationSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Schedule\RequestedAssignmentSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Schedule\RequestedFreePeriodSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Teacher\TeacherBusySuggestion;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Teacher\TeacherRequestedTimeSlotSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Teacher\TeacherUnavailableSuggestion;

class BlockerRegistry
{
    protected $handlers = [
       HallBusySuggestion::class,
       CourseRequestedTimeSlotSuggestion::class,
       HallRequestedTimeSlotSuggestion::class,
       BreakPeriodSuggestion::class,
       OperationalPeriodSuggestion::class,
       PeriodDurationSuggestion::class,
       RequestedAssignmentSuggestion::class,
       RequestedFreePeriodSuggestion::class,
       TeacherBusySuggestion::class,
       TeacherRequestedTimeSlotSuggestion::class,
       TeacherUnavailableSuggestion::class
    ];

    public function generateBlockerSuggestions(array $blockers): array {
        $suggestions = [];
        foreach($blockers as $blocker){
            foreach ($this->handlers as $handlerClass) {
                $handler = new $handlerClass();
                if($handler->support($blocker->type)){
                    $suggestions[] = $handler->getBlockerSuggestion($blocker);
                    break;
                }
            }
        }
        return $suggestions;
    }
}
