<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\Contracts;

interface ConstraintSuggestion
{
    public static function type(): string;
    public function suggest(array $constraintModification): array;
}
