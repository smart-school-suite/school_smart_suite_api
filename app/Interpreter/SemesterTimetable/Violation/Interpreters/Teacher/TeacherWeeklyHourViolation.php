<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherWeeklyHours;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Teacher;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class TeacherWeeklyHourViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return TeacherWeeklyHours::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $entity = $blocker->entity ?? null;
        $conflict = $blocker->conflict ?? null;
        $evidence = $blocker->evidence ?? null;
        $teacher = Teacher::find($entity['teacher_id'] ?? null);
        return "Max Teacher Weekly Hours Violation: Scheduling A Session from";
    }
}
