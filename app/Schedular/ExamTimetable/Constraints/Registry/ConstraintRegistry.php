<?php

namespace App\Schedular\ExamTimetable\Constraints\Registry;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Handlers\Course\CourseRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Handlers\Invigilator\InvigilatorRequestedSlot;
use App\Schedular\ExamTimetable\Core\State;

class ConstraintRegistry
{
    protected $handlers = [
        RequestedAssignment::class,
        CourseRequestedSlot::class,
        InvigilatorRequestedSlot::class,
        HallRequestedSlot::class
    ];

    public function enforce(State $state)
    {
        foreach ($this->handlers as $handlerClass) {
            $handler = new $handlerClass();
            if ($handler instanceof ConstraintHandler) {
                $handler->handle($state);
            }
        }
    }
}
