<?php

namespace App\Interpreter\SemesterTimetable\Core;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\Core\InterpreterRegistry;
use App\Interpreter\SemesterTimetable\Core\FallBackInterpreter;
class DiagnosticResponseBuilder
{
    protected InterpreterRegistry $registry;
    protected ConstraintInterpreter $constraintInterpreter;
    protected FallBackInterpreter $fallBackInterpreter;
    public function __construct(
        InterpreterRegistry $registry,
        ConstraintInterpreter $constraintInterpreter,
        FallBackInterpreter $fallBackInterpreter
    ) {
        $this->registry = $registry;
        $this->constraintInterpreter = $constraintInterpreter;
        $this->fallBackInterpreter = $fallBackInterpreter;
    }

    public function build(array $diagnostics): array
    {
        return collect($diagnostics)
            ->map(function ($diagnostic) {
                $constraint = $diagnostic['constraint_failed']['constraint'];

                $interpreter = $this->registry->resolve($constraint);

                return $interpreter
                    ? $interpreter->interpret($diagnostic)
                    : $this->fallBackInterpreter->interpret($diagnostic);
            })
            ->toArray();
    }
}
