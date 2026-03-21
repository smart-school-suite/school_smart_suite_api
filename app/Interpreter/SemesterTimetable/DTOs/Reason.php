<?php

namespace App\Interpreter\SemesterTimetable\DTOs;

use App\Models\Constraint\SemTimetableBlocker;

class Reason
{
    public function __construct(
        public SemTimetableBlocker $violation,
        public string $title,
        public string $description,
        public array $context = []
    ) {}
}
