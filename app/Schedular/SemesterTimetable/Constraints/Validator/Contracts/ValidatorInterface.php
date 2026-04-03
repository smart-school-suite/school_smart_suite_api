<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Contracts;

use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;

interface ValidatorInterface
{
    /**
     * Returns a structured blocker record if the condition is violated,
     * or null if the check passes.
     */
    public function check(ConstraintContext $context, array $params): ?array;
}
