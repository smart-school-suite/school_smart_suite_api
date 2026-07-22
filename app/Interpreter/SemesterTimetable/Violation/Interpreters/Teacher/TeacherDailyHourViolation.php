<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherDailyHours;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Teacher;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class TeacherDailyHourViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return TeacherDailyHours::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $entity = $blocker->entity ?? null;
        $conflict = $blocker->conflict ?? null;
        $evidence = $blocker->evidence ?? null;
        $teacher = Teacher::find($entity['teacher_id'] ?? null);

        return "Max Teacher Daily Hours Violation: Sheduling A Session from ";
    }
}
