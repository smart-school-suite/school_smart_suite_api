<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\DTO;

use App\Models\Constraint\SemTimetableConstraint;

class ConstraintSuggestionDTO
{
    public function  __construct(
        public SemTimetableConstraint $constraint,
        public string $summary,
        public array $context
    ) {}
}
