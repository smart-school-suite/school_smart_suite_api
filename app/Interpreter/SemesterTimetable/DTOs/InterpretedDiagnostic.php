<?php

namespace App\Interpreter\SemesterTimetable\DTOs;

class InterpretedDiagnostic
{
    public function __construct(
        public string $summary,
        public string $constraint,
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
