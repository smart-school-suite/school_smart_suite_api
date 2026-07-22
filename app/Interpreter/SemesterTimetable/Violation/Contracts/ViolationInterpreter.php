<?php

namespace App\Interpreter\SemesterTimetable\Violation\Contracts;

use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

interface ViolationInterpreter
{
    public static function type(): string;
    public function explain(BlockerDTO $blocker): string;
}
