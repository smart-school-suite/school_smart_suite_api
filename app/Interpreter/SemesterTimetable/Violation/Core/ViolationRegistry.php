<?php

namespace App\Interpreter\SemesterTimetable\Violation\Core;

use App\Constant\Violation\SemesterTimetable\Builder\ViolationBuilder;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;

class ViolationRegistry
{
    protected array $map = [];
    public function __construct()
    {
        $this->map = ViolationBuilder::violationHandlerMap();
    }
    public function resolve(string $violation): ?ViolationInterpreter
    {
        return isset($this->map[$violation])
            ? app($this->map[$violation])
            : null;
    }
}
