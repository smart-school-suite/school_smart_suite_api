<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\Contracts;

interface SuggestionGenerator
{
    public static function supports(string $blockerType): bool;

    public function generate(array $diagnostic, array $blocker): array;
}
