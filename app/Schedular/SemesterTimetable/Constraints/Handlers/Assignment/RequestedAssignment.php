<?php

namespace App\Schedular\SemesterTimetable\Constraints\Handlers\Assignment;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodViolation;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodViolation;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationViolation;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy as TeacherBusyViolation;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable as TeacherUnavailableViolation;
use App\Constant\Violation\SemesterTimetable\Hall\HallBusy as HallBusyViolation;
use App\Schedular\SemesterTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Core\State;

class RequestedAssignment implements ConstraintHandler
{
    public static function supports(): string
    {
        return RequestedAssignmentConstraint::KEY;
    }

    public function handle(array $requestPayload, State $state): void
    {
        $context = ConstraintContext::fromPayload($requestPayload);
        $requestedAssignments = $context->requestedAssignments();



    }

}
