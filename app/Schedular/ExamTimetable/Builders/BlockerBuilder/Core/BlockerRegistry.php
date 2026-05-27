<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Core;

use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Assignment\RequestedAssignmentBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Course\CourseRequestedSlotBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall\HallCapacityBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall\HallRequestedSlotBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator\InvigilatorRequestedSlotBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule\OperationalPeriodBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule\SessionDurationBlocker;

class BlockerRegistry
{
    protected $map = [
       RequestedAssignmentBlocker::class,
       CourseRequestedSlotBlocker::class,
       HallCapacityBlocker::class,
       HallRequestedSlotBlocker::class,
       InvigilatorRequestedSlotBlocker::class,
       OperationalPeriodBlocker::class,
       SessionDurationBlocker::class
    ];

    public function build(array $blocker){
       $violations = collect();
       foreach($this->map as $blockerHandler){
           $handler = new $blockerHandler();
           if($handler->supports($blocker['key'])){
               $violations->push($handler->build($blocker));
           }
       }
       return $violations;
    }
}
