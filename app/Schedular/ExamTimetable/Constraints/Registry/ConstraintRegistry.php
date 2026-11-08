<?php

namespace App\Schedular\ExamTimetable\Constraints\Registry;

use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Handlers\Assignment\RequestedAssignment;
use App\Schedular\ExamTimetable\Constraints\Handlers\Course\CourseRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Handlers\Hall\HallRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Handlers\Invigilator\InvigilatorRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Handlers\Student\StudentDailyLoadRange;
use App\Schedular\ExamTimetable\Core\State;

/**
 * Soft constraints, in the order they are applied.
 *
 * An exam course is placed once, so requests that fix a course in place run
 * before the placement engine (a course moved afterwards would leave a gap).
 * Hall and invigilator requests are used by placement as preferences and
 * checked afterwards, together with the daily load range.
 */
class ConstraintRegistry
{
    protected array $handlers = [
        RequestedAssignment::class,
        CourseRequestedSlot::class,
        HallRequestedSlot::class,
        InvigilatorRequestedSlot::class,
        StudentDailyLoadRange::class,
    ];

    public function enforce(State $state, string $stage): void
    {
        foreach ($this->handlers as $handlerClass) {
            if ($handlerClass::stage() !== $stage) {
                continue;
            }

            $handler = app($handlerClass);
            if ($handler instanceof ConstraintHandler) {
                $handler->handle($state);
            }
        }
    }
}
