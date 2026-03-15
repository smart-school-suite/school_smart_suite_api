<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\DTO;

class ConstraintSuggestionDTO
{
    public function  __construct(
        public string $summary,
        public array $context
    ) {}
}
