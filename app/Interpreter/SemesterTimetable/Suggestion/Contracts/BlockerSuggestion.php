<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\Contracts;

interface BlockerSuggestion
{
    public static function type(): string;
    public function suggest(array $blockerResolution): array;
}
