<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Assignment;

use App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment;
use App\Interpreter\SemesterTimetable\DTOs\DiagnosticContext;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\Teacher;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class RequestedAssignmentViolation extends DiagnosticContext implements ViolationInterpreter
{
    public static function type(): string
    {
        return RequestedAssigment::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $conflict = $blocker->conflict;
        $existingRequestedAssignment = $blocker->entity;
        return "Existing Requested Assignment,";
    }
}
