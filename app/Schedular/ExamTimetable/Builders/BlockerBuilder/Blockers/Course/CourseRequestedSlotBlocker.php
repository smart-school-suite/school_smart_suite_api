<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot as CourseRequestedSlotConstraint;
use App\Constant\Violation\ExamTimetable\Course\CourseRequestedSlot as CourseRequestedSlotViolation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\ExamTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;
use Override;

class CourseRequestedSlotBlocker implements BlockerBuilder
{
    #[Override]
    public function supports(string $type): bool
    {
        return $type === CourseRequestedSlotViolation::KEY;
    }

    #[Override]
    public function build(array $blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->id = app(GenerateId::class)->generateId(array_filter([
            "type" => CourseRequestedSlotConstraint::KEY,
            'course_id'  => $blocker['course_id'] ?? null,
            'date'        => $blocker['date'] ?? null,
            'start_time' => $blocker['start_time'] ?? null,
            'end_time'   => $blocker['end_time'] ?? null
        ]));
        $violation->type =  CourseRequestedSlotViolation::KEY;
        $violation->entity = array_filter([
            "type" => CourseRequestedSlotConstraint::KEY,
            'course_id'  => $blocker['course_id'] ?? null,
            'date'        => $blocker['date'] ?? null,
            'start_time' => $blocker['start_time'] ?? null,
            'end_time'   => $blocker['end_time'] ?? null
        ]);
        $violation->conflict = array_filter([...$blocker['conflict']]);
        return $violation;
    }
}
