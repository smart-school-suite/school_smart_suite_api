<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\DTO;

class BlockerSuggestionDTO
{
    public function  __construct(
        public string $summary,
        public array $context
    ) {}
}
