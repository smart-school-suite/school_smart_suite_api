<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\Registry;

use App\Constant\Constraint\SemesterTimetable\Builder\ConstraintBuilder;

class ConstraintSuggestionRegistry
{
    protected array $map = [];
    public function __construct()
    {
        $this->map = ConstraintBuilder::constraintSuggestionMap();
    }
    public function get(string $constraint): ?string
    {
        return $this->map[$constraint] ?? null;
    }
}
