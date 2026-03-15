<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\Core;

class SuggestionEngine
{
    public function __construct(
        private ConstraintSuggestionEngine $constraintEngine,
        private BlockerResolutionEngine $blockerEngine
    ) {}

    public function generate(array $suggestions): array
    {
        return [
            'constraint_modification' =>
            $this->constraintEngine->generate($suggestions['constraint_modification']),

            'blocker_resolution' =>
            $this->blockerEngine->generate($suggestions['blocker_resolution'])
        ];
    }
}
