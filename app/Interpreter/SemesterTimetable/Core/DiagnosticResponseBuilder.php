<?php

namespace App\Interpreter\SemesterTimetable\Core;


use App\Interpreter\SemesterTimetable\Core\InterpreterRegistry;
use App\Interpreter\SemesterTimetable\Core\FallBackInterpreter;

class DiagnosticResponseBuilder
{
    protected InterpreterRegistry $registry;
    protected FallBackInterpreter $fallBackInterpreter;
    public function __construct(
        InterpreterRegistry $registry,
        FallBackInterpreter $fallBackInterpreter
    ) {
        $this->registry = $registry;
        $this->fallBackInterpreter = $fallBackInterpreter;
    }

    public function build(array $diagnostics): array
    {
        return collect($diagnostics)
            ->map(function ($diagnostic) {
                $constraintFailed = $diagnostic->constraint_failed['type'];
                $interpreter = $this->registry->resolve($constraintFailed);

                return $interpreter
                    ? $interpreter->interpret($diagnostic)
                    : $this->fallBackInterpreter->interpret($diagnostic);
            })
            ->toArray();
    }
}
