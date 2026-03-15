<?php

namespace App\Interpreter\SemesterTimetable\Core;

use App\Constant\Constraint\SemesterTimetable\Builder\ConstraintBuilder;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;

class InterpreterRegistry
{
    protected array $map = [];
    public function __construct()
    {
        $this->map = ConstraintBuilder::constraintInterpreterMap();
    }
    public function resolve(string $constraint): ?ConstraintInterpreter
    {
        return isset($this->map[$constraint])
            ? app($this->map[$constraint])
            : null;
    }
}
