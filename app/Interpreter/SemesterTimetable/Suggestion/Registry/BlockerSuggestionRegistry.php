<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\Registry;

use App\Constant\Violation\SemesterTimetable\Builder\ViolationBuilder;

class BlockerSuggestionRegistry
{
    private array $map = [];
    public function  __construct()
    {
        $this->map = ViolationBuilder::violationSuggestionHandlerMap();
    }

    public function get(string $violation): ?string
    {
        return $this->map[$violation] ?? null;
    }
}
