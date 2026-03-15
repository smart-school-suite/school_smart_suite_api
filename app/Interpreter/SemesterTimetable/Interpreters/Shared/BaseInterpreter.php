<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Shared;

use App\Constant\Violation\SemesterTimetable\Builder\ViolationBuilder;
use App\Interpreter\SemesterTimetable\Violation\Core\ViolationRegistry;
use App\Interpreter\SemesterTimetable\DTOs\Reason;
use App\Interpreter\SemesterTimetable\Suggestion\Core\SuggestionEngine;

class BaseInterpreter
{
    private ViolationRegistry $violationRegistry;
    private SuggestionEngine $suggestionEngine;

    public function __construct(ViolationRegistry $violationRegistry, SuggestionEngine $suggestionEngine)
    {
        $this->violationRegistry = $violationRegistry;
        $this->suggestionEngine = $suggestionEngine;
    }
    public function buildReason(array $blockers): array
    {
        $reasons = [];
        foreach ($blockers as $blocker) {
            $violation = $this->violationRegistry
                ->resolve($blocker['type']);

            if ($violation) {
                $reasons[] = new Reason(
                    title: ViolationBuilder::title($blocker['type']),
                    description: $violation->explain($blocker)
                );
            }
        }
        return $reasons;
    }

    public function buildSuggestion(array $suggestions): array
    {
        return $this->suggestionEngine->generate($suggestions);
    }
}
