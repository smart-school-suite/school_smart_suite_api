<?php

namespace App\Interpreter\SemesterTimetable\DTOs;

use App\Models\Constraint\SemTimetableConstraint;

class InterpretedDiagnostic
{
    public function __construct(
        public string $summary,
        public SemTimetableConstraint $constraint,
        public string $severity = 'hard',
        public array $reasons = [],
        public array $suggestions = []
    ) {}

    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
            'constraint' => $this->constraint,
            'severity' => $this->severity,
            'reasons' => $this->reasons,
            'suggestions' => $this->suggestions,
        ];
    }
}
