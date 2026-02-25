<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Shared;

use App\Constant\Violation\SemesterTimetable\Builder\ViolationBuilder;
use App\Interpreter\SemesterTimetable\Violation\Core\ViolationRegistry;
use App\Interpreter\SemesterTimetable\DTOs\Reason;

class BasedInterpreter
{
    private ViolationRegistry $violationRegistry;

    public function __construct(ViolationRegistry $violationRegistry)
    {
        $this->violationRegistry = $violationRegistry;
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
}
