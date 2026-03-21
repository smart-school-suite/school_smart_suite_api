<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\DTO;

use App\Models\Constraint\SemTimetableBlocker;

class BlockerSuggestionDTO
{
    public function  __construct(
        public SemTimetableBlocker $blocker,
        public string $summary,
        public array $context
    ) {}
}
