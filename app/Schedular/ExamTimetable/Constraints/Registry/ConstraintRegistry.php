<?php

namespace App\Schedular\ExamTimetable\Constraints\Registry;

use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Handlers\Assignment\RequestedAssignment;
use App\Schedular\ExamTimetable\Constraints\Handlers\Course\CourseRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Handlers\Hall\HallRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Handlers\Invigilator\InvigilatorRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Handlers\Student\StudentDailyLoadRange;
use App\Schedular\ExamTimetable\Core\State;

class ConstraintRegistry
{
    /** Order matters: requested assignments are pinned before course time requests. */
    protected array $handlers = [
        RequestedAssignment::class,
        CourseRequestedSlot::class,
        InvigilatorRequestedSlot::class,
        HallRequestedSlot::class,
        StudentDailyLoadRange::class,
    ];

    public function enforce(State $state, string $phase): void
    {
        foreach ($this->handlers as $handlerClass) {
            $handler = app($handlerClass);

            if ($handler instanceof ConstraintHandler && $handler->phase() === $phase) {
                $handler->handle($state);
            }
        }
    }
}
